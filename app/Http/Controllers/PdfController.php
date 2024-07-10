<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Spatie\LaravelPdf\PdfBuilder;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;

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

    // if (app()->environment('local')) {
    //   return Pdf::view('pdf.invoice-pdf', compact('invoice'))
    //     ->withBrowsershot(function (Browsershot $browsershot) {
    //       $browsershot
    //         ->noSandbox()
    //         ->setNodeBinary('C:/Program Files/nodejs %~dp0;%PATH%;')
    //         ->setNodeBinary('/usr/bin/node')->setNpmBinary('/usr/bin/npm')
    //       ;
    //     })
    //     ->margins(10, 0, 10, 0)
    //     ->format(Format::A2)
    //     ->name("{$invoice->code}_{$invoice->order->customer->name}_{$invoice->order->trip_date->translatedFormat('d-m-Y')}.pdf");
    // }

    return view('pdf.invoice-pdf', compact('invoice'));
  }
}
