<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\View\View;
use Spatie\LaravelPdf\PdfBuilder;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;

class PdfController extends Controller
{
  /**
   * Generates a PDF file of an invoice.
   *
   * @param string $code The code of the invoice to generate.
   * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the invoice with the given code is not found.
   */
  public function invoice(Invoice $invoice)
  {
    $pdfName = "{$invoice->code}_{$invoice->order->customer->name}_{$invoice->order->trip_date->translatedFormat('d-m-Y')}.pdf";

    if (app()->environment('local')) {
      return Pdf::view('pdf.invoice-pdf', compact('invoice'))
        // ->withBrowsershot(function (Browsershot $browsershot) {
        // $browsershot
        // ->noSandbox()
        // ->setNodeBinary('C:/Program Files/nodejs %~dp0;%PATH%;')
        // ->setNodeBinary('/usr/bin/node')->setNpmBinary('/usr/bin/npm')
        // ;
        // })
        ->margins(10, 0, 10, 0)
        ->format(Format::A2)
        ->name("{$invoice->code}_{$invoice->order->customer->name}_{$invoice->order->trip_date->translatedFormat('d-m-Y')}.pdf");
    }

    // return DomPDF::loadView('pdf.invoice-pdf', compact('invoice'))
    //   ->setPaper('a2', 'landscape')
    //   ->stream($pdfName);

    return view('pdf.invoice-pdf', compact('invoice'));
  }
}
