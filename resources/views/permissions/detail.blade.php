@extends('layouts.admin')

@section('content')
    <style>
        /* تنسيق العرض داخل المتصفح */
        @media print {
            @page { 
                size: A5 landscape; 
                margin: 0; 
            }
            nav, .no-print { 
                display: none !important; 
            }
            body { 
                background: white; 
                direction: rtl; 
            }
        }
        
        .font-arabic {
            font-family: 'Arial', sans-serif;
        }
    </style>

    <div class="px-2 sm:px-4 lg:px-2 no-print font-arabic" dir="rtl">
        <nav class="mb-6 overflow-x-auto text-right">
            <ol class="flex items-center justify-start gap-2 text-xs sm:text-sm whitespace-nowrap flex-row-reverse">
                <li>
                    <a href="{{ route('permissions.index') }}" class="text-gray-500 hover:text-[#4B0082] flex items-center gap-1">
                        <i class="fas fa-user-clock text-xs"></i> الإجازات
                    </a>
                </li>
                <li class="text-gray-400"><</li>
                <li class="px-2 sm:px-3 py-1 bg-[#4B0082]/10 text-[#4B0082] rounded-lg flex items-center gap-2">
                    <i class="fas fa-eye text-xs"></i> تفاصيل الإجازة
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow border p-4 sm:p-6 mb-6 text-right">
            <h2 class="text-lg sm:text-xl font-bold text-[#4B0082]">
                تفاصيل الإجازة - {{ $posseders->first()->personnel->type_personnel == 'militaire' ? 'عسكري' : 'طالب' }}
            </h2>
            <p class="text-gray-500 text-xs sm:text-sm">
                معلومات الإجازة ولائحة الأفراد المستفيدين
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow border p-4 sm:p-6 text-right">
            <h3 class="text-base sm:text-lg font-semibold text-[#4B0082] mb-4 flex items-center gap-2 justify-start">
                <i class="fas fa-users"></i> الأفراد المصاحبون
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[600px] border rounded-lg text-right">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2">الرتبة</th>
                            <th class="p-2">الاسم</th>
                            <th class="p-2">النسب</th>
                            <th class="p-2">رقم التأشيرة</th>
                            <th class="p-2">تاريخ البدء</th>
                            <th class="p-2">تاريخ الانتهاء</th>
                            <th class="p-2">النوع</th>
                            <th class="p-2">الوجهة</th>
                            <th class="p-2 text-center no-print">طباعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posseders as $posseder)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-2">{{ $posseder->personnel->grade->libelle_grade }}</td>
                                <td class="p-2">{{ $posseder->personnel->nom }}</td>
                                <td class="p-2">{{ $posseder->personnel->prenom }}</td>
                                <td class="p-2">{{ $posseder->personnel->matricule }}</td>
                                <td class="p-2">{{ $posseder->date_début ? \Carbon\Carbon::parse($posseder->date_début)->format('d/m/Y') : '-' }}</td>
                                <td class="p-2">{{ $posseder->date_fin ? \Carbon\Carbon::parse($posseder->date_fin)->format('d/m/Y') : '-' }}</td>
                                <td class="p-2">{{ $posseder->motif->libelle_motif ?? '-' }}</td>
                                <td class="p-2">{{ $posseder->ville->nom_ville ?? '-' }}</td>
                                <td class="p-2 text-center no-print">
    @php
        $dernierAvis = $posseder->permission->avisPermissions->last();
    @endphp

    @if (
        $dernierAvis &&
        $dernierAvis->personnel &&
        $dernierAvis->personnel->fonction &&
        $dernierAvis->personnel->fonction->nom_fonction == 'COMMANDANT CIT' &&
        $dernierAvis->avis == 'favorable'
    )
        <button onclick="printRow(this)"
            class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
            <i class="fas fa-print"></i>
        </button>
    @else
        <span class="text-red-400 text-xs text-nowrap">
            في انتظار موافقة قائد المركز
        </span>
    @endif
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-6 text-center text-gray-400">لا يوجد أفراد مرتبطون بهذه الإجازة</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

               @if (
    $posseders->count() > 1 &&
    $dernierAvis &&
    $dernierAvis->personnel &&
    $dernierAvis->personnel->fonction &&
    $dernierAvis->personnel->fonction->nom_fonction == 'COMMANDANT CIT' &&
    $dernierAvis->avis == 'favorable'
)
                    <div class="text-left mt-4">
                        <button onclick="printAllRows()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 no-print">
                            <i class="fas fa-print ml-2"></i> طباعة جميع الرخص
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function printRow(button) {
            let row = button.closest("tr");
            let cells = row.querySelectorAll("td");
            let logoUrl = '{{ asset('images/citlog.png') }}';
            generatePrint([cells], logoUrl);
        }

        function printAllRows() {
            let rows = document.querySelectorAll("tbody tr");
            let logoUrl = '{{ asset('images/citlog.png') }}';
            let all = [];
            rows.forEach(row => {
                let btn = row.querySelector('button');
                if(btn) all.push(row.querySelectorAll("td"));
            });
            generatePrint(all, logoUrl);
        }

        function generatePrint(rows, logoUrl) {
            let permissions = "";
            rows.forEach(cells => {
                permissions += `
                <div class="permission-page">
                    <div class="main-container">
                        <div class="right-side">
                            <div class="header-official">
                                <p>   المملكة المغربية</p>
                                <p>القوات المسلحة الملكية</p>
                            <p>الحامية العسكرية: القنيطرة</p>
                         <p>الوحدة: مركز التدريب لسلاح الإشارة</p>
                                <p style="margin-right: 25px;">  </p>
                            </div>

                            <div class="logo-box">
                                <img src="${logoUrl}">
                                <div class="rank-title">${cells[0].innerText}</div>
                            </div>

                            <div class="doc-title">إجازة : ${cells[6].innerText}</div>
                            
                            <div class="info-content">
                                <div class="info-row"><span class="label">الاسم الشخصي والعائلي :</span><span class="dots"><b>${cells[1].innerText} ${cells[2].innerText}</b></span></div>
                                <div class="info-row"><span class="label">الرتبة :</span><span class="dots"><b>${cells[0].innerText}</b></span></div>
                                
                                <div style="display:flex; gap:10px;">
                                    <div class="info-row" style="flex:1.5"><span class="label" style="min-width:85px">صالحة من :</span><span class="dots"><b>${cells[4].innerText}</b></span></div>
                                    <div class="info-row" style="flex:1"><span class="label" style="min-width:40px">إلى :</span><span class="dots"><b>${cells[5].innerText}</b></span><span style="margin-right:5px">مضمن</span></div>
                                </div>

                                <div class="info-row"><span class="label">للذهاب من :</span><span class="dots"><b>القنيطرة</b></span><span style="margin:0 10px">إلى :</span><span class="dots"><b>${cells[7].innerText}</b></span></div>
                                
                                <div class="info-row">
                                    <span>بالقنيطرة في :</span>
                                    <span class="dots" style="text-align: center;"><b>{{ date('d/m/Y') }}</b></span>
                                </div>
                            </div>
                            
                            <div class="signature-block">
                                <p>العقيد هشام الكراري</p>
                                <p>قائد مركز التدريب لسلاح الإشارة</p>
                                <p>للقوات المسلحة الملكية</p>
                                <div style="height: 10px;"></div>
                                <p>إمضاء العقيد هشام الكراري بالنيابة</p>
                            </div>
                        </div>

                        <div class="left-side">
                            <div class="mini-box">
                                رخصة تخول لحاملها الحق في التعريفة<br>
                                العسكرية في السكك الحديدية على<br>
                                المسافات المبينة<br>
                                <span style="font-size: 11px;">(مع الحصول على تذكرة مقابل الأداء)</span>
                                <hr style="border: 0; border-top: 1px solid #000; margin: 5px 0;">
                                <b>الدرجة الثانية</b>
                            </div>

                            <div class="terms-box">
                                1- يتعين تقديم هذه الوثيقة متى طلبها أفراد الدرك الملكي أو الأمن الوطني أو أعوان النقل السككي.<br><br>
                                2- في حالة التعبئة أو استدعاء المستفيدين يلتحق حامل الرخصة بوحدته دون انتظار استدعاء فردي إلا إذا كان في رخصة نقاهة.<br><br>
                                3- إذا نزل المستفيد بمستشفى، تحسب مدة الاستشفاء ضمن أمد الغياب. يجب على المستفيد بعد انتهاء رخصته أن يلتحق بوحدته أو مصلحته إلا إذا حاز رخصة جديدة.
                            </div>

                            <div class="form-model">
                                نموذج 24/3 ق م م
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            let content = `
            <html dir="rtl">
            <head>
                <style>
                    @page { size: A5 landscape; margin: 0; }
                    body { font-family: 'Arial', sans-serif; margin: 0; padding: 5mm; background: white; }
                    .permission-page { page-break-after: always; height: 138mm; box-sizing: border-box; }
                    .main-container { display: flex; width: 100%; height: 100%; border: none; }
                    
                    /* تنسيق الجانب الأيمن */
                    .right-side { width: 70%; padding: 10px; border-left:none; display: flex; flex-direction: column; }
                    .header-official p { margin: 0; line-height: 1.2; font-size: 12px; font-weight: bold; }
                    .logo-box { text-align: center; margin-top: -10px; }
                    .logo-box img { height: 65px; }
                    .rank-title { font-weight: bold; font-size: 17px; margin-top: 5px; }
                    .doc-title { font-weight: bold; font-size: 20px; text-decoration: underline; text-align: center; margin: 10px 0; }
                    
                    .info-content { flex-grow: 1; }
                    .info-row { display: flex; margin: 10px 0; font-size: 15px; align-items: baseline; }
                    .label { min-width: 140px; white-space: nowrap; font-weight: bold; }
                    .dots { flex-grow: 1; border-bottom: 1px dotted #000; padding-right: 5px; min-height: 1.2em; }
                    
                    .signature-block { text-align: center; font-weight: bold; font-size: 14px; margin-top: auto; padding-left: 50px; }
                    .signature-block p { margin: 2px 0; }

                    /* تنسيق الجانب الأيسر */
                    .left-side { width: 30%; padding: 10px; display: flex; flex-direction: column; }
                    .mini-box { border: 1px solid #000; padding: 8px; text-align: center; font-size: 13px; line-height: 1.5; margin-bottom: 10px; }
                    .terms-box { border: 1px solid #000; padding: 10px; font-size: 12px; line-height: 1.6; text-align: justify; flex-grow: 1; }
                    .form-model { font-weight: bold; margin-top: 10px; text-align: left; font-size: 11px; }
                </style>
            </head>
            <body>
                ${permissions}
                <script>window.onload = function(){ window.print(); window.onafterprint = function(){ window.close(); } }<\/script>
            </body>
            </html>`;

            let printWindow = window.open('', '_blank');
            printWindow.document.write(content);
            printWindow.document.close();
        }
    </script>
@endsection