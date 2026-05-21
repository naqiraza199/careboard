<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BillingReport;
use App\Models\Event;
use App\Models\Shift;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Illuminate\Support\Facades\Auth;

class InvoicePaymentController extends BaseController
{
    public function store(Request $request, Invoice $invoice)
{
    $validated = $request->validate([
        'paid_amount'  => 'nullable',
        'reference'    => 'nullable',
        'payment_date' => 'nullable',
    ]);

    DB::transaction(function () use ($invoice, $validated) {
        $payment = InvoicePayment::create([
            'invoice_id'   => $invoice->id,
            'paid_amount'  => $validated['paid_amount'] ?? 0,
            'reference'    => $validated['reference'] ?? null,
            'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
        ]);

        $newBalance = max(0, (float) $invoice->balance - (float) $payment->paid_amount);
        $invoice->balance = $newBalance;
        if ($newBalance == 0.0) {
            $invoice->status = 'Paid';
        }
        $invoice->save();

        Event::create([
            'invoice_id' => $invoice->id, 
            'invoice_payment_id' => $payment->id,
            'title'      => auth()->user()->name . ' Received Payment',
            'from'       => 'Invoice Payment'. $payment->paid_amount . ". ",
            'body' => auth()->user()->name . " received payment of $" . $payment->paid_amount . ". ",
        ]);
    });

    Notification::make()
        ->title('Payment Added')
        ->body('Payment added successfully')
        ->success()
        ->send();

    return back()->with('success', 'Payment recorded successfully.');
}




 public function destroy(InvoicePayment $invoicePayment)
{
    $invoice = $invoicePayment->invoice;

    DB::transaction(function () use ($invoicePayment, $invoice) {
        $amount = (float) $invoicePayment->paid_amount;

        // Delete the event linked to this payment
        // \App\Models\Event::where('invoice_payment_id', $invoicePayment->id)->delete();

        // Delete the payment
        $invoicePayment->delete();

        // Update invoice balance
        $invoice->balance = (float) $invoice->balance + $amount;
        if ($invoice->balance == 0.0) {
            $invoice->status = 'Paid';
        }
        $invoice->save();

        Event::create([
            'invoice_id' => $invoice->id, 
            'invoice_payment_id' => $invoicePayment->id,
            'title'      => auth()->user()->name . ' Deleted Payment',
            'from'       => 'Invoice Payment'. $invoicePayment->paid_amount . ". ",
            'body' => auth()->user()->name . " deleted payment of $" . $invoicePayment->paid_amount . ". ",
        ]);
    });

    Notification::make()
        ->title('Payment Deleted')
        ->body('Payment and related event deleted successfully')
        ->success()
        ->send();

    return back()->with('success', 'Payment deleted successfully.');
}

public function addNote(Request $request, Invoice $invoice)
{
    $request->validate([
        'note' => 'required|string|max:1000',
    ]);

    \App\Models\Event::create([
        'invoice_id' => $invoice->id,
        'title'      => auth()->user()->name . ' added a note',
        'from'       => 'Invoice Note',
        'body'       => $request->note,
    ]);

    Notification::make()
        ->title('Note Added')
        ->body('Your note has been saved to the invoice.')
        ->success()
        ->send();

    return back()->with('success', 'Note added successfully.');
}
public function update(Request $request, Invoice $invoice)
{
    $request->validate([
        'additional_contact_id' => 'nullable|exists:additional_contacts,id',
        'payment_due'           => 'nullable|date',
        'ref_no'                => 'nullable|string|max:255',
        'purchase_order'        => 'nullable|string|max:255',
        'description'           => 'nullable|array',
        'description.hour_shift.*' => 'required|string',
        'description.km_shift.*'   => 'nullable|string',
    ]);

    $description = $request->description ?? [];
    // Remove empty lines
    $description['hour_shift'] = array_filter($description['hour_shift'] ?? [], fn($v) => trim($v) !== '');
    $description['km_shift']   = array_filter($description['km_shift'] ?? [], fn($v) => trim($v) !== '');


    $paymentDue = null;
    if ($request->payment_due) {
        try {
            // Try different date formats
            $paymentDue = Carbon::createFromFormat('d-m-Y', $request->payment_due)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                $paymentDue = Carbon::parse($request->payment_due)->format('Y-m-d');
            } catch (\Exception $e2) {
                // Keep as null if parsing fails
            }
        }
    }

    $invoice->update([
        'additional_contact_id' => $request->additional_contact_id,
        'payment_due' => $paymentDue,
        'ref_no' => $request->ref_no,
        'purchase_order' => $request->purchase_order,
        'description' => !empty($description) ? $description : null,
    ]);


    \App\Models\Event::create([
        'invoice_id' => $invoice->id,
        'title'      => auth()->user()->name . ' Updated Invoice',
        'from'       => 'Invoice',
        'body'       => 'Invoice details and description updated',
    ]);

    \Filament\Notifications\Notification::make()
        ->title('Invoice Updated')
        ->success()
        ->send();

    return back();
}

     public function void(Request $request, Invoice $invoice)
    {
        if ($invoice->is_void) {
            return back()->with('error', 'Invoice already voided.');
        }

        // Get the billing report IDs linked to this invoice
        $billingReportIds = is_string($invoice->billing_reports_ids)
            ? json_decode($invoice->billing_reports_ids, true)
            : ($invoice->billing_reports_ids ?? []);

        // Get shift IDs from those billing reports
        $shiftIds = BillingReport::whereIn('id', $billingReportIds)
            ->pluck('shift_id')
            ->filter()
            ->unique()
            ->toArray();

        // Revert billing reports back to Unpaid
        BillingReport::whereIn('id', $billingReportIds)->update([
            'status' => 'Unpaid',
        ]);

        // Revert shifts back to Approved
        Shift::whereIn('id', $shiftIds)->update([
            'status' => 'Booked',
        ]);

        // Mark invoice as void
        $invoice->update([
            'is_void' => 1,
        ]);

        // Log event
        Event::create([
            'invoice_id' => $invoice->id,
            'title'      => Auth::user()->name . ' Voided Invoice',
            'from'       => 'Invoice',
            'body'       => 'Invoice voided — billing reports reverted to Unpaid, shifts reverted to Approved.',
        ]);

        Notification::make()
            ->title('Invoice Voided')
            ->body('Invoice voided. Billing reports set back to Unpaid and shifts set back to Approved.')
            ->success()
            ->send();

        return redirect()->route('filament.admin.pages.invoice-list');
    }



public function sendEmail(Invoice $invoice)
{
    try {
        // Find correct recipient (check additional_contact first, then client)
        $email = optional($invoice->additional_contact)->email 
              ?? optional($invoice->client)->email 
              ?? null;

        if (!$email) {
            Notification::make()
                ->title('Email Error')
                ->body('No email found for this client.')
                ->danger()
                ->send();

            return back()->with('error', 'No email found for this client.');
        }

        // Send mail with PDF attachment
        Mail::to($email)->send(new InvoiceMail($invoice));

        // Mark invoice as mailed
        $invoice->update([
            'send_mail' => 1,
        ]);

        // Record event
        Event::create([
            'invoice_id' => $invoice->id,
            'title'      => Auth::user()->name . ' sent invoice email',
            'from'       => 'Invoice Email',
            'body'       => Auth::user()->name . " emailed Invoice #{$invoice->id} to {$email}.",
        ]);

        Notification::make()
            ->title('Invoice Sent')
            ->body("Invoice has been emailed to {$email}.")
            ->success()
            ->send();

        return back()->with('success', "Invoice sent successfully to {$email}");
    } catch (\Exception $e) {
        Notification::make()
            ->title('Email Failed')
            ->body('Failed to send invoice: ' . $e->getMessage())
            ->danger()
            ->send();

        return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
    }
}


}



