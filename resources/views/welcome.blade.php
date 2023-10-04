@extends('layouts.master')

@section('title')
{{ trans('main.site_title') }}

@endsection

@section('content')
            <div class="panel panel-default">
                <div class="panel-body">
                    <h1>Murreh</h1>
                    <p>Добро пожаловать на сайт диалектной базы карельского языка MURREH!</p>

                    <p>Настоящий ресурс позволит вам познакомиться с говорами карельского языка Карелии, Тверской, Ленинградской и Новгородской областей, 
                        а также сравнить их между собой.</p>

                    <p>В разделе <a href="/ques/anketas/">ВОПРОСНИК</a> представлены карельские диалектные материалы, полученные в полевых условиях сотрудниками Карельского научно-исследовательского института 
                        (с 1945 года Института истории, языка и литературы, а с 1956 года Института языка, литературы и истории) в 1937–1959 годах в ходе работы над 
                        «Диалектологическим атласом карельского языка» (под руководством профессора Д. В. Бубриха). 
                        Рукописные «Программы по собиранию материала для диалектологического атласа карельского языка» хранятся в Научном архиве Карельского научного центра РАН.
                        В настоящее время ведется работа по оцифровке и вводу диалектных данных программ в базу.
                    </p>

                    <p>В разделе <a href="/sosd/concept_place/">СОСД</a> предлагаем вам познакомиться с карельскими и вепсскими диалектными материалами «Сопоставительно-ономасиологического словаря 
                        диалектов карельского, вепсского и саамского языков» (2007, под общ. ред. Ю. С. Елисеева, Н. Г. Зайцевой).</p>

                    <p><a href="http://resources.krc.karelia.ru/krc/doc/publ2008/onomasiolog_slovar.pdf" target="_parent">Сопоставительно-ономасиологический словарь диалектов карельского, вепсского, саамского языков</a>. Под общей редакцией Ю.С.Елисеева и Н.Г.Зайцевой. 
                        Петрозаводск: КарНЦ РАН, 2007. 348 с.</p>

                    <p>Словарь подготовлен сотрудниками сектора языкознания Института языка, литературы и истории Карельского научного центра РАН на основе данных, 
                        собранных в 1979–1981 годы в условиях экспедиционной работы в 24 карельских, 6 вепсских и 5 саамcких пунктах (саамские материалы в базе данных не приводятся). 
                        Словарь представлен тремя крупными лексико-семантическими темами «Вселенная», «Человек» и «Человек и вселенная», каждая из которых включает целый ряд разделов. 
                        Для <a href="/sosd/concept_category/">каждого раздела</a> приведены кластерные карты, демонстрирующие диалектные ареалы, сформированные внутри карельского и вепсского языков на основе 
                        определенного лексико-семантического разряда. <a href="https://karjalankieliopit.net/atlas/bubon/bubon.html">Программа кластеризации</a> 
                        разработана профессором информатики университета Восточной Финляндии Мартти Пенттоненом.</p>
                    
                    <div class='row' style='margin-bottom: 20px'>
                        <div class='col-sm-3'>
                            <a href='/images/big/svid_bd_2022.png'><img src='/images/svid_bd_2022.png'></a>
                        </div>
                        <div class='col-sm-3' style='padding-top: 30px;'>
                            Cвидетельство о государственной регистрации базы данных Федеральной службы по интеллектуальной собственности<br>
                            № 2022621259<br> 
                            <a href='https://new.fips.ru/registers-doc-view/fips_servlet?DB=DB&DocNumber=2022621259&TypeFile=html'>"Диалектная база карельского языка MURREH"</a><br>
                            от 23.05.2022
                        </div>
                        <div class='col-sm-3'>
                            <a href='/images/big/svid_cluster_2022.png'><img src='/images/svid_cluster_2022.png'></a>
                        </div>
                        <div class='col-sm-3' style='padding-top: 30px;'>
                            Cвидетельство о государственной регистрации программы для ЭВМ Федеральной службы по интеллектуальной собственности<br>
                            № 2022660015<br> 
                            <a href='https://new.fips.ru/registers-doc-view/fips_servlet?DB=EVM&DocNumber=2022660015&TypeFile=html'>"Clustering n-dimensional heterogeneous language data for dialect partitioning"</a><br>
                            от 27.05.2022
                        </div>
                    </div>

                    <h2>Участники проекта</h2>

                    <p><a href="http://nataly.krc.karelia.ru/">Крижановская Наталья Борисовна</a><br>
                    ведущий инженер-исследователь лаб. информационных компьютерных технологий ИПМИ КарНЦ РАН</p>

                    <p><a href="http://illhportal.krc.karelia.ru/member.php?id=745&plang=r">Новак Ирина Петровна</a><br>
                    к.фил.н., научный сотрудник сектора языкознания ИЯЛИ КарНЦ РАН</p>
                    
                    <h2>Статистика</h2>                   
                    <p>Количество анкет: <b id="total-anketas"></b><br>
                    Количество собирателей: <b id="total-recorders"></b><br>
                    Количество населенных пунктов: <b id="total-places"></b><br>
                    Количество ответов (всего): <b id="total-answers-all"></b><br>
                    Количество ответов (социолингвистическая информация): <b id="total-answers-1"></b><br>
                    Количество ответов (фонетика): <b id="total-answers-2"></b><br>
                    Количество ответов (морфология): <b id="total-answers-3"></b><br>
                    Количество ответов (лексика): <b id="total-answers-4"></b></p>
                </div>
            </div>
@endsection

@section('footScriptExtra')
    {!!Html::script('js/stats.js')!!}
@stop

@section('jqueryFunc')
    totalAnketas();
    totalRecorders();
    totalPlaces();
    totalAnswers('all');
    totalAnswers(1);
    totalAnswers(2);
    totalAnswers(3);
    totalAnswers(4);
@stop