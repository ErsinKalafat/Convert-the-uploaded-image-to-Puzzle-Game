<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <title>Yazlab2Proje1 - Puzzle Bulmaca </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .puzzle {
            position: relative;
            margin: 1px auto;
            padding: 1px;
            text-align: center;
        }

        body {
            background: #fbfbfe;
            text-align: center;
        }

        h2 {
            margin: 5px auto;
            color: crimson;
            font-weight: bold;
            font-family: "Courier New";
        }
    </style>
</head>
<body>
<h2>Yazılım Laboratuvarı II - Proje 1</h2>
<h4>Ersin Kalafat - 130202117</h4>
<hr>


<?php

$uygunFormatlar = array('jpg', 'jpeg', 'png', 'gif');
$yuklenenFormat = strtolower(substr(strrchr($_FILES['dosya']['name'], '.'), 1));

if (in_array($yuklenenFormat, $uygunFormatlar)) {
    $dilim = explode(".", $_FILES['dosya']['name']);
    $dosyaYolu = "resimler/ornek." . $dilim[1];
    move_uploaded_file($_FILES['dosya']['tmp_name'], $dosyaYolu);
} else {
    echo 'Dosya formatı uygun değil.';
}


?>
<form method="POST" action="index.php" enctype="multipart/form-data">
    <label for="file"> Resim Seçiniz : </label>
    <input type="file" name="dosya">
    <input style="width:120px; height: 50px; font-size: 20px; font-weight: bold; color: white; background-color: #274e63"
           type="submit" value="Yükle">
</form>
<hr>
<button style="width:120px; height: 50px; font-size: 20px; font-weight: bold; color: white; background-color: crimson"
        onclick="location.reload();">KARIŞTIR
</button>
En Yüksek Skor : 90
<hr>
<div id="bulmacam" class="puzzle">Puzzle</div>
<script>


    var bulmacam = new PuzzleBulmaca('bulmacam', 'resimler/ornek.jpg', 4, 4, 500, 500);

    bulmacam.cozuldu = function () {
        alert('Tebrikler! Puzzle\'ı  ' + bulmacam.clicks / 2 + ' tıklamada çözdün!');

        var skor = bulmacam.clicks / 2;

        var txtFile = "skor.txt";
        var file = new File(txtFile);
        file.open("w");
        file.write(skor);
        file.close();
    };


    function PuzzleBulmaca(resim_ID, resim, sutunlar, satirlar, wg, hg) {
        var me = this;
        var img = '';
        var icerikAlani = '';
        var width = wg ? wg : '';
        var height = hg ? hg : '';
        var sutunlar = sutunlar;
        var satirlar = satirlar;
        var parcaBoyutu = '';
        var parcaSayisi = '';
        var parcaKoordinat = [];
        var resimParcali = [];
        var tiklananParca = -1;
        var cozulduMu = 0;
        me.clicks = 0;

        function elemanAyarlari(resim_ID, resim) {
            //özellik ayarları 1
            img = resim;
            if (width == '') width = img.width;
            if (height == '') height = img.height;

            //canvas eklenmesi
            var parent = document.getElementById(resim_ID);
            parent.innerHTML = '<canvas id="' + resim_ID + '_cnv" width="' + width + '" height="' + height + '" class="puzzleAlani"></canvas>';

            //özellik ayarları 2
            var cnv = document.getElementById(resim_ID + '_cnv');
            icerikAlani = cnv.getContext('2d');
            parcaBoyutu = {w: img.naturalWidth / sutunlar, h: img.naturalHeight / satirlar};
            parcaSayisi = {w: width / sutunlar, h: height / satirlar};

            resimParcaAyarla();

            cnv.addEventListener('click', function (ev) {
                if (cozulduMu == 0) { //eğer tamamlanmadıysa
                    me.clicks++;
                    var x = ev.offsetX;
                    var y = ev.offsetY;

                    //$resimParcali den tıklanan parçanın belirlenmesi
                    for (var id in resimParcali) {
                        if (y > resimParcali[id].ty && y < resimParcali[id].ty + parcaSayisi.h && x > resimParcali[id].tx && x < resimParcali[id].tx + parcaSayisi.w) {
                            //eğer ilk tıklanan parçaysa çerçeve çiz, değilse yer değiştir.
                            if (tiklananParca == -1) {
                                tiklananParca = id;
                                cerceve(8, '#000000', id);
                            } else {
                                var ikinciTiklanan = {
                                    tx: resimParcali[id].tx,
                                    ty: resimParcali[id].ty,
                                    ord: resimParcali[id].ord
                                };// 2. tıklanan 1. tıklanana yüklenmeli (yer değiştirmeli)
                                resimParcali[id] = {
                                    px: resimParcali[id].px,
                                    py: resimParcali[id].py,
                                    tx: resimParcali[tiklananParca].tx,
                                    ty: resimParcali[tiklananParca].ty,
                                    ord: resimParcali[tiklananParca].ord,
                                    id: id
                                };
                                resimParcali[tiklananParca] = {
                                    px: resimParcali[tiklananParca].px,
                                    py: resimParcali[tiklananParca].py,
                                    tx: ikinciTiklanan.tx,
                                    ty: ikinciTiklanan.ty,
                                    ord: ikinciTiklanan.ord,
                                    id: tiklananParca
                                };  //1st tl
                                parcalariGoster(resimParcali);
                                tiklananParca = -1;
                            }
                            break;
                        }
                    }
                }
            });

        }

        function resimParcaAyarla() {
            for (var i = 0; i < sutunlar * satirlar; ++i) {
                var c = Math.floor(i / satirlar);
                var r = i % satirlar;

                parcaKoordinat.push({
                    px: c * parcaBoyutu.w,
                    py: r * parcaBoyutu.h,
                    tx: c * parcaSayisi.w,
                    ty: r * parcaSayisi.h,
                    id: i
                });
            }
            for (var j, x, i = parcaKoordinat.length; i; j = Math.floor(Math.random() * i), x = parcaKoordinat[--i], parcaKoordinat[i] = parcaKoordinat[j], parcaKoordinat[j] = x) ;  //shuffle array
            parcalariAyarla();
        }

        function parcalariAyarla() {
            for (var i = 0; i < parcaKoordinat.length; i++) {
                var c = Math.floor(i / satirlar);
                var r = i % satirlar;
                resimParcali[parcaKoordinat[i].id] = {
                    px: parcaKoordinat[i].px,
                    py: parcaKoordinat[i].py,
                    tx: c * parcaSayisi.w,
                    ty: r * parcaSayisi.h,
                    ord: i
                };
            }
            parcalariGoster(resimParcali);
        }

        function parcalariGoster(resimParcali) {
            for (var id in resimParcali) {
                icerikAlani.drawImage(img, resimParcali[id].px, resimParcali[id].py, parcaBoyutu.w, parcaBoyutu.h, resimParcali[id].tx, resimParcali[id].ty, parcaSayisi.w, parcaSayisi.h);
            }
            basariKontrol();
        }


        function basariKontrol() {
            var re = 1;
            if (cozulduMu == 0) {
                for (var id in resimParcali) {
                    if (id != resimParcali[id].ord) {
                        re = 0;
                        break;
                    }
                }
            }
            if (re == 1) {
                icerikAlani.drawImage(img, 0, 0, width, height);

                if (cozulduMu == 0) {
                    cozulduMu = 1;
                    me.cozuldu();
                }
            }
        }

        function cerceve(boyut, renk, id) {
            icerikAlani.lineWidth = boyut;
            icerikAlani.strokeStyle = renk;
            icerikAlani.strokeRect(resimParcali[id].tx + 1, resimParcali[id].ty + 1, parcaSayisi.w - 2, parcaSayisi.h - 2);
        }

        if (typeof resim == 'string') {
            img = new Image();
            img.onload = function () {
                elemanAyarlari(resim_ID, img);
            };
            img.src = resim;
        } else {
            resim.outerHTML = '<div id="' + resim_ID + 'e" class="puzzle"></div>';
            elemanAyarlari(resim_ID + 'e', resim);
        }
    }


</script>

</body>
</html>