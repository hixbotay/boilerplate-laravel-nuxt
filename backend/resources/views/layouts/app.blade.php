<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sellonboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <style>
        @keyframes float {
            0% {
                transform: translatey(0px);
            }

            50% {
                transform: translatey(-20px);
            }

            100% {
                transform: translatey(0px);
            }
        }

        body {
            font-family: Poppins, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        button {
            color: #FFFFFF;
            background-color: #6c5dd3;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 400;
            margin-top: 30px;
            cursor: pointer;
        }
        .title {
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .description {
            font-size: 14px;
            color: #7f7f7f;
        }

        .loader-logo {
            margin-bottom: 20px;
            overflow: hidden;
            transform: translatey(0px);
            animation: float 3s ease-in-out infinite;
        }

        .loader-logo svg {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        #url {
            display: none;
        }
    </style>
</head>

<body>
    <div>
        <div class="loader-logo">
            <svg width="1526" height="1526" viewBox="0 0 1526 1526" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M113 1.60004C111.1 2.50004 104.8 5.00004 99 7.20004C83.7 12.9 73.4 18.1 61.6 26C36.1 43.1 16.8 69.5 6.5 101.4C4.6 107.4 2.3 113.4 1.5 114.6C0.2 116.7 0 162 0 513.7C0 905.5 0 910.6 2 914.5C3 916.7 5.3 923.2 7.1 928.9C13.5 949.7 24.5 967.5 41.5 984.6C63.5 1006.7 90.5 1020.5 122.6 1026C133.8 1028 138 1028.1 196.9 1028C263.8 1027.9 264.3 1027.9 267.2 1022.8C268.3 1020.9 268.6 964.5 269 702C269.5 402.3 269.7 382.9 271.3 373.1C276.1 345.3 286.3 325.4 305.4 306.5C323.7 288.3 344.4 277.2 371 271.4C377.9 269.9 405.8 269.7 697.5 269.5C902 269.3 1017.4 268.8 1019 268.2C1020.4 267.7 1022.4 266.2 1023.5 265C1025.5 262.8 1025.5 261.4 1025.5 193.6C1025.5 125.7 1025.4 124.3 1023.2 114.3C1012.1 63.7 978.8 26 929.5 8.00004C924.6 6.20004 918.1 3.70004 915.1 2.40004L909.8 3.6861e-05H513.1C155.7 0.100037 116.2 0.200037 113 1.60004Z"
                    fill="#E1092A" />
                <path
                    d="M714.5 498C631.3 498.6 629.2 498.6 617 500.9C593.7 505.2 579 510.5 564.3 519.7C551.7 527.5 546.5 531.8 535.8 542.8C514.3 564.9 501.1 592.5 498 621.4C497.2 628.2 496.8 695.1 496.7 825.7L496.5 1019.8L498.6 1022.7C502.9 1028.5 490 1028.1 699.8 1028.1C911.4 1028.1 903.3 1028.3 923.8 1022.4C947 1015.7 972.7 999.6 991.2 980.2C1005.6 965 1014.3 950.4 1021 930.1C1029 905.8 1028.4 923.3 1028.8 704.4C1029 548.2 1028.8 508.5 1027.8 505.5C1025.2 497.7 1033.9 498.3 910 497.8C849.2 497.5 761.3 497.6 714.5 498Z"
                    fill="#E1092A" />
                <path
                    d="M1260.9 498.5C1259.4 499.3 1257.8 500.7 1257.4 501.7C1256.9 502.7 1256.3 646.2 1256 824.5L1255.4 1145.5L1253.3 1154.5C1247.3 1180.4 1236.6 1200.1 1219.2 1217.8C1200.4 1236.9 1178.7 1248.2 1151 1253.2C1139.6 1255.2 1137.8 1255.2 823 1255.7C525.3 1256.1 506.3 1256.2 503.5 1257.9C497.3 1261.4 497.5 1259.2 497.5 1330.5C497.5 1403 497.6 1404.3 504.7 1426C511.8 1448.1 522.8 1465.8 540 1483.1C558 1501.2 572.6 1510 602.7 1520.9L616.8 1526H1012.8H1408.8L1425.3 1519.5C1445.5 1511.7 1452.8 1508 1465.1 1499.9C1490.4 1483 1509.4 1456.6 1520.1 1423.8C1522.2 1417.2 1524.4 1411.2 1525 1410.5C1525.7 1409.7 1525.9 1277.4 1525.8 1010.9L1525.5 612.5L1522.7 604.5C1516.2 586.2 1512.3 577.3 1507.4 568.9C1486.5 533.5 1454.1 509.6 1414 499.7C1405.9 497.7 1402.5 497.6 1334.5 497.3C1273.7 497.1 1263.1 497.2 1260.9 498.5Z"
                    fill="#E1092A" />
            </svg>
        </div>
        @yield('content')
    </div>
</body>

</html>