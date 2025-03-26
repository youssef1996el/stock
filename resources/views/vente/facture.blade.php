<!DOCTYPE html>
<html>
<head>
    <title>Bon de Commande</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            font-family: DejaVu Sans !important;
        }
       
        @page {
            size: a4;
            margin: 0;
            padding: 0;
        }
        .invoice-box table {
            direction: ltr;
            width: 100%;
            text-align: right;
            border: 1px solid;
            font-family: 'DejaVu Sans', 'Roboto', 'Montserrat', 'Open Sans', sans-serif;
        }
        .row, .column {
            display: block;
            page-break-before: avoid;
            page-break-after: avoid;
        }
    </style>
    <style>
        .invoice-container {
            height: 1060px;
            position: relative;
            border: 1px solid;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #ffffff; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        }
        .left {
            width: 50%;
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
        }
        /* .right {
            width: 50%;
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
        } */
        .titleLeft {
            border: 1px solid rgb(150, 196, 255);
            border-radius: 10px;
        }
        .DivContentInformationClient {
            border: 1px solid rgb(150, 196, 255);
            border-radius: 10px;
            width: 95%;
        }
        .container {
            display: flex;
            width: 98%;
            margin: 20px;
            box-sizing: border-box;
        }
        #tableDetail {
            width: 100%;
            border-collapse: collapse;
           /*  margin-top: 100px; */
            font-size: 12px;
        }
        #tableDetail th,
        #tableDetail td {
            border: 1px solid;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        #tableDetail th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
            white-space: nowrap;
        }
        .invoice-footer {
            text-transform: uppercase;
            white-space: nowrap;
            margin-top: 5px;
            bottom: 12;
            position: absolute;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 48%;
            transform: translate(-50%, -50%) rotate(-45deg); 
            font-size: 200px;
            opacity: 0.1;
            pointer-events: none;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <img src="data:image/png;base64,{{ $imageData }}" alt="" width="750px">
       
        <div class="container">
            <div style="display: flex;justify-content: center;text-align: center;font-family: 'Amiri', serif;">
                <h3>Bon de commande formateur</h3>
            </div>
        </div>
        <div>
            <div class="container ">
               
                <table id="tableDetail">
                    <tr>
                        <th>   
                            Type de Commande :
                        </th>
                        <th>
                        formateur:
                        </th>
                    </tr>
                    <tr>
                        <th>   
                            Entité :
                        </th>
                        <th>
                            Date : {{ \Carbon\Carbon::parse($Data_Vente[0]->created_at)->format('d/m/Y') }}
                        </th>
                    </tr>
                </table>


                <table id="tableDetail" style="margin-top: 30px">
                    <thead>
                        <tr>
                            <td style="text-align: center"><strong>Désignations</strong></td>
                            <td style="text-align: center"><strong>Quantité Commandée</strong></td>
                            <td style="text-align: center"><strong>Quantité Livrée</strong></td>
                            <td style="text-align: center"><strong>Observations</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($Data_Vente as $item)
                            <tr>
                                <td style="text-align: center">{{ $item->name }}</td>
                                <td style="text-align: center">{{ $item->qte }}</td>
                                <td style="text-align: center">{{ $item->qte }}</td>
                                <td style="text-align: center"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table id="tableDetail" style="margin-top: 30px">
                    <thead>
                        <tr>
                            <td style="text-align: center"><strong>A la Commande (Date + Signature)</strong></td>
                            <td style="text-align: center"><strong>Validation</strong></td>
                            <td style="text-align: center"><strong>A la Livraison</strong></td>
                            <td style="text-align: center"><strong>A la Réception (Date + Signature)</strong></td>
                        </tr>
                    </thead>
                    <tbody>
                       
                        <tr>
                            <td style="text-align: center"></td>
                            <td style="text-align: center"></td>
                            <td style="text-align: center"></td>
                            <td style="text-align: center"></td>
                        </tr>
                       
                    </tbody>
                </table>
            </div>
        </div>
        
        
        
       
        <footer>
            <div class="invoice-footer">
                <img src="data:image/png;base64,{{ $imageData_bottom }}" alt="" width="750px">
              
            </div>
        </footer>
      
    </div>
    
    
    

</body>

</html>