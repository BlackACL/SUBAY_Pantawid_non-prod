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

        .header-table {
            width: 100%;
            margin-bottom: 8px;
            font-family: Arial, sans-serif;
        }

        .header-table td {
            padding: 2px;
            vertical-align: middle;
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
    <table class="header-table">
        <tr>
            <td style="width: 80px; vertical-align: middle;">
                <img src="{{ public_path('images/dswd_logo.png') }}" style="height: 60px; width: auto;">
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <div style="font-size: 13pt; font-weight: bold;">ADMINISTRATIVE DIVISION</div>
                <div style="font-size: 10pt; font-weight: bold;">FIELD OFFICE {{ $field_office }}</div>
                <div style="font-size: 8pt;">DSWD-AS-GF-003 | REV 02 / 07 OCT 2022</div>
            </td>
            <td style="width: 80px;"></td>
        </tr>
    </table>
    <hr class="header-line">

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
        <hr class="footer-line">
        <p style="text-align: center; font-size: 7.5pt; margin: 5px 0; font-family: Arial, sans-serif;">
            DSWD Field Office XI, (address), Philippines (Zip Code)<br>
            Website: http://www.dswd.gov.ph Tel Nos.: __________ Telefax: __________
        </p>
    </div> 
  
</body>
</html>
