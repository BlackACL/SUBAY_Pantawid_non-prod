<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        @page {
            size: letter landscape;
            margin: 0.8cm 1.5cm 1.5cm 1.5cm;
            margin-bottom: 3.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            margin: 0;
            padding: 0 0 30px 0;
        }



        h3 {
            font-size: 13.5pt;
            margin: 5px 0 5px 0;
            text-align: center;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
            font-size: 9pt;
        }

        th {
            font-weight: bold;
            text-align: left;
        }

        .no-border,
        .no-border td,
        .no-border th {
            border: none !important;
        }

        .section-title {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 6px 0 3px 0;
        }

        .page-number {
            text-align: right;
            font-size: 9pt;
            margin-bottom: 3px;
        }

        .header-line {
            border-top: 2px solid #000;
            margin: 5px 0 3px 0;
        }

        .footer-line {
            border-top: 2px solid #000;
            margin: 8px 0 5px 0;
        }

        .header-container {
            width: 100%;
            margin-bottom: 2px;
        }

        .footer-container {
            position: fixed;
            bottom: -0.7cm;
            left: 0;
            right: 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header Container -->
    <div class="header-container">
        <!-- Header Area -->
        <div style="display: flex; align-items: center; margin-top: 0px; margin-bottom: 2px; font-family: 'Times New Roman', Times, serif;">
            <div style="flex: 0 0 auto;">
                <img src="{{ public_path('images/pdf_logo.png') }}" style="height: 2.0cm; width: auto; " alt="DSWD Logo">
            </div>
            <div style="flex: 1; display:flex; justify-content:flex-end; font-weight: bold;">
                <div style="display: flex; flex-direction: column; text-align: center; line-height: 1.15; margin-right: -600px; margin-top: -60px;">
                    <div style="font-size: 13pt;">ADMINISTRATIVE DIVISION</div>
                    <div style="font-size: 10pt;">FIELD OFFICE {{ $field_office }}</div>
                    <div style="font-size: 8pt; font-weight: normal;">DSWD-AS-GF-003 | REV 02 / 07 OCT 2022</div>
                </div>
            </div>
        </div>
        <hr class="header-line">
    </div>

    <h3 style="margin-top: 10px;">FURNITURE AND EQUIPMENT TRANSFER SLIP (FETS)</h3>

    <!-- FETS Info and Property Data Title -->
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px;">
        <p class="section-title" style="margin: 0; flex: 1;">PROPERTY DATA</p>
        <div style="text-align: right; font-size: 9pt; white-space: nowrap;">
            FETS No.: <u>{{ $fets_no }}</u> &nbsp;&nbsp;&nbsp; FETS Date: <u>{{ $fets_date }}</u>
        </div>
    </div>

    <!-- Property Data -->
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Property No.</th>
                <th style="width: 15%;">Serial Number</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 18%;">PAR No.</th>
                <th style="width: 7%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="font-size: 8pt;">{{ $item['property_no'] }}</td>
                <td style="font-size: 8pt;">{{ $item['serial_no'] }}</td>
                <td style="font-size: 8pt;">{{ $item['description'] }}</td>
                <td style="font-size: 8pt;">{{ $item['par_no'] }}</td>
                <td style="font-size: 8pt;">{{ $item['remarks'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Type of Movement -->
    <p class="section-title" style="margin-top: 10px;">TYPE OF MOVEMENT</p>
    <table class="no-border" style="margin-bottom: 15px;">
        <tr>
            <td style="width: 20px; border: 1px solid #000; text-align: center; font-size: 9pt">[ ]</td>
            <td style="padding-left: 5px; font-size: 9pt;">{{ $movement_type }}</td>
        </tr>
    </table>

    <!-- Transaction Details -->
    <p class="section-title">TRANSACTION DETAILS</p>
    <table>
        <tr>
            <th style="width: 3%;"></th>
            <th style="width: 14%;">Name of Office</th>
            <th style="width: 25%;">Name of Accountable Person</th>
            <th style="width: 14%;">SubPAR/SubICS</th>
            <th style="width: 30%;">SubPAR/SubICS Contract Date (if applicable)</th>
        </tr>
        <tr>
            <td style="font-size: 8pt;">From</td>
            <td style="font-size: 8pt;">{{ $from_office }}</td>
            <td style="font-size: 8pt;">{{ $from_person }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="font-size: 8pt;">To</td>
            <td style="font-size: 8pt;">{{ $to_office }}</td>
            <td style="font-size: 8pt;">{{ $to_person }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Authorities -->
    <table class="no-border" style="margin-top: 12px; font-size: 9pt;">
        <tr>
            <td style="width: 20%;">Transfer requested by:<br><br><strong>{{ $requested_by }}</strong><br><span style="font-size: 8pt;">(Position / Office)</span></td>
            <td style="width: 20%;">Recommending Authority:<br><br><strong>{{ $recommended_by }}</strong><br><span style="font-size: 8pt;">(Position / Office)</span></td>
            <td style="width: 20%;">Approving Authority:<br><br><strong>{{ $approved_by }}</strong><br><span style="font-size: 8pt;">(Position / Office)</span></td>
            <td style="width: 20%;">Witnessed/Inspected by:<br><br><strong>{{ $inspected_by }}</strong><br><span style="font-size: 8pt;">(Position / Office)</span></td>
            <td style="width: 20%;">Property Received by:<br><br><strong>{{ $received_by }}</strong><br><span style="font-size: 8pt;">(Position / Office)</span></td>
        </tr>
    </table>

    <!-- Property Recording -->
    <p class="section-title" style="margin-top: 10px;">PROPERTY RECORDING (for PSAMD / FO Property Office / Section use only)</p>
    <table>
        <tr>
            <th style="width: 20%;"></th>
            <th style="width: 25%;">Name</th>
            <th style="width: 25%;">Signature</th>
            <th style="width: 12.5%;">Date</th>
            <th style="width: 12.5%;">Remarks</th>
        </tr>
        <tr>
            <td>Inspected by:</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Stored by:</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Encoded to PREMIS by:</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="footer-container">
        <div style="margin-bottom: -100px;">
        <script type="text/php">
            if (isset($pdf)) {
                $text = "PAGE {PAGE_NUM} of {PAGE_COUNT}";
                $font = $fontMetrics->getFont("Arial", "bold");
                $size = 7;
                $y = $pdf->get_height() - 90;
                $x = ($pdf->get_width() - $fontMetrics->getTextWidth($text, $font, $size)) / 2 + 25;
                $pdf->page_text($x, $y, $text, $font, $size, array(0, 0, 0));
            }
        </script>
        
        <hr class="footer-line">
        
        <!-- Footer -->
        <p style="text-align: center; font-size: 7.5pt; margin-top: 8px; margin-bottom: 2px; font-family: 'Times New Roman', Times, serif; line-height: 1.3;">
            DSWD Field Office _, (address), Philippines (Zip Code)<br>
            Website: http://www.dswd.gov.ph Tel Nos.: ________ Telefax: ________
        </p>
    </div>
   </div> 
  
</body>
</html>