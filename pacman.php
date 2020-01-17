<?php
    $browser=$_SERVER['HTTP_USER_AGENT'];
    if (substr($browser,0,strlen("Opera"))!=="Opera" &&
               substr($browser,0,strlen("Mozilla/5.0"))!=="Mozilla/5.0")
        exit("Please access this URL with a proper browser!\n");
    ?>
<!--
Koristeni programski jezici i preporuceni materijali za ucenje:
Javacript - https://www.w3schools.com/js/default.asp
SVG - https://www.w3schools.com/graphics/svg_intro.asp
PHP (vrti se na serveru) - https://www.w3schools.com/php/default.asp
HTML - https://www.w3schools.com/html/default.asp
CSS (inline na nekoliko mjesta) - https://www.w3schools.com/css/default.asp
-->
<html lang="en">
    <head>
        <title>PacMan in Javacript</title>
        <meta name="author" content="Teo Samarzija">
        <meta name="description" content="A PacMan game made using SVG and JavaScript, playable on most smartphones.">
        <meta name="keywords" content="Retrocomputing, HTML5, PacMan, SVG, JavaScript">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<!-- Sprijeci zoomiranje na smartphonima (ako netko slucajno dodirne neko mjesto u labirintu dvaput umjesto jedanput). -->
    </head>
    <body>
        <?php
        $datoteka=fopen("pachigh.txt","r");
        $highscore=fgets($datoteka)*1;
        $player=fgets($datoteka);
        fclose($datoteka);
        if (strpos($player," ") || strpos($player,"<") || strpos($player,">") || strpos($player,"&") || strpos($player,"\t") || strpos($player,"&gt;") || strlen($player)==0)
            $player="anonymous";
        ?>
        <center>
        <svg width=300 height=450 style="background:black;" id="zaslon">
        <defs>
        <linearGradient id="leftGradient" x1="0%" x2="100%" y1="0%" y2="0%">
        <!-- Boja lijeve tipke. -->
        <stop offset="0%" stop-color="gray"/>
        <stop offset="100%" stop-color="white"/>
        </linearGradient>
        <linearGradient id="rightGradient" x1="0%" x2="100%" y1="0%" y2="0%">
        <!-- Boja desne tipke. -->
        <stop offset="0%" stop-color="white"/>
        <stop offset="100%" stop-color="gray"/>
        </linearGradient>
        <linearGradient id="upGradient" x1="0%" x2="0%" y1="0%" y2="100%">
        <!-- Boja gornje tipke. -->
        <stop offset="0%" stop-color="gray"/>
        <stop offset="100%" stop-color="white"/>
        </linearGradient>
        <linearGradient id="downGradient" x1="0%" x2="0%" y1="0%" y2="100%">
        <!-- Boja donje tipke. -->
        <stop offset="0%" stop-color="white"/>
        <stop offset="100%" stop-color="gray"/>
        </linearGradient>
        </defs>
        <rect x=130 y=360
              fill="url(#upGradient)"
              onMouseOver="this.style.fill = 'lightGray'"
              onMouseOut="this.style.fill = 'url(#upGradient)'"
              width=40 height=40 onClick="onButtonUp()"></rect>
        <!-- Gornja tipka -->
        <rect x=85 y=405
              fill="url(#leftGradient)"
              onMouseOver="this.style.fill = 'lightGray'"
              onMouseOut="this.style.fill = 'url(#leftGradient)'"
              width=40 height=40 onClick="onButtonLeft()"></rect>
        <!-- Lijeva tipka -->
        <rect x=130 y=405
              fill="url(#downGradient)"
              onMouseOver="this.style.fill = 'lightGray'"
              onMouseOut="this.style.fill = 'url(#downGradient)'"
              width=40 height=40 onClick="onButtonDown()"></rect>
        <!-- Donja tipka -->
        <rect x=175 y=405
              fill="url(#rightGradient)"
              onMouseOver="this.style.fill = 'lightGray'"
              onMouseOut="this.style.fill = 'url(#rightGradient)'"
              width=40 height=40 onClick="onButtonRight()"></rect>
        <!-- Desna tipka -->
        <text x=175 y=385 fill="white"
              style="font-size: 18px; font-family:'Lucida Console'"
              id="score">Score: 0</text>
        <rect x=15 y=415 width=65 height=20 fill="red" onClick="onStartButton()"
              id="startButton"></rect>
        <text x=20 y=430 fill="orange" id="startText" onClick="onStartButton()">START!</text>
        </svg><br>
<div id="bodovi" style="width:300px; line-height:50px; font-family: Lucida; background-color: yellow; text-align:center; margin-bottom:5px;">Highscore: <i><?php echo $highscore; ?></i> by <i><?php echo $player; ?></i>.</div>
The game does NOT respond to keyboard buttons. On smartphones, the Pacman is supposed to follow your finger, to go in the direction where you last tapped. In case that doesn't work, you have buttons below the maze. On computers, it's playable by mouse.<br/>You can see the source code, with the comments in Croatian, <a href="https://github.com/FlatAssembler/SVG-Pacman/blob/master/pacman.php">here</a>.
        </center>
        <script type="text/javascript">
            window.setTimeout(function(){document.body.removeChild(document.body.children[document.body.children.length-1]);},1000); //Ukloni "Powered by 000webhost", da ne smeta na smartphonima.
            var finished=false; //Je li igra zavrsila.
            var highscore=<?php echo $highscore; ?>; //Ovaj podatak u JavaScript kod umece PHP program koji se vrti na serveru.
            var lives = 3, time1 = 0, time2 = 0, score = 0, blue = -100 /*"Plavi" duhovi se mogu "pojesti". Duhovi prestanu biti plavi nakon sto prode odredeno vrijeme (koje se broji varijablom 'brojac').*/, maxp = 0 /*Broj tocaka u svakom nivou, PacMan ih mora pojesti sve da prijede na iduci nivo.*/, points = 0 /*Broj tocaka koje je PacMan do sada pojeo.*/;
            var level = 0;
            var svgNS = "http://www.w3.org/2000/svg"; //Ovo je potrebno jer SVG ne koristi HTML DOM, nego XML DOM s drugim namespaceom.
            var brojac = 0, brojac1 = 0, brojac2=0; //Brojaci koji se koriste u glavnoj petlji i u animacijskoj petlji.
            var changed = false; //Je li za vrijeme izvrsavanja animacijske petlje promijenjen smijer kretanja PacMan-a.
            var pocetno = []; //Labirint na pocetku igre (polje stringova).
            /*
             W - zid
             B - velika tockica (koja, kada ju PacMan pojede, ucini da duhovi poplave).
             P - mala tockica (donosi bodove, te, ako ih PacMan sve pojede, prelazi na iduci level).
             C - PacMan (gdje se nalazi na pocetku nivoa)
             1 - crveni duh
             2 - ruzicasti duh
             3 - narancasti duh
             */
            pocetno[ 0] = "               ";
            pocetno[ 1] = " WWWWWWWWWWWWW ";
            pocetno[ 2] = " WBPPPPWPPPPBW ";
            pocetno[ 3] = " WPWWWPWPWWWPW ";
            pocetno[ 4] = " WPW WPWPW WPW ";
            pocetno[ 5] = " WPWWWPWPWWWPW ";
            pocetno[ 6] = " WPPPPP PPPPPW ";
            pocetno[ 7] = "WWWWPWW WWPWWWW";
            pocetno[ 8] = "    PW123WP    ";
            pocetno[ 9] = "WWWWPWWWWWPWWWW";
            pocetno[10] = "   WPP C PPW   ";
            pocetno[11] = " WWWPWWWWWPWWW ";
            pocetno[12] = " WPPPPPPPPPPPW ";
            pocetno[13] = " WPWWWWPWWWWPW ";
            pocetno[14] = " WPW  WPW  WPW ";
            pocetno[15] = " WPWWWWPWWWWPW ";
            pocetno[16] = " WPPPPPBPPPPPW ";
            pocetno[17] = " WWWWWWWWWWWWW ";
            pocetno[18] = "               ";
            //Smijerovi kretanja. Smijer '0' znaci 'gore', smijer '1' znaci 'desno', smijer '2' znaci 'dolje', smijer '3' znaci 'lijevo', dok smijer '4' znaci 'stoji'.
            var sx = [0, 1, 0, -1, 0];
            var sy = [-1, 0, 1, 0, 0];
            //Smijerovi kretanja duhova i pacmana. Crveni ce se na pocetku nivoa kretati desno, ruzicasti gore, a narancasti lijevo. Pacman ce se kretati desno.
            var gs = [1, 0, 3], ps = 1;
            var opx, opy, ogx = [], ogy = []; //'opx' i 'opx' su koordinate PacMana na pocetku nivoa, a 'ogx' i 'ogy' su koordinate duhova na pocetku nivoa.
            var px = 0; //Trenutna x-koordinata PacMana.
            var py = 0; //Trenutna y-koordinata PacMana.
            var gx = []; //Trenutna x-koordinata duhova.
            var gy = []; //Trenutna y-koordinata duhova.
            var removed = [false, false, false]; //Je li Pacman nedavno pojeo tog duha.
            var colors = ["red", "pink", "orange"]; //Boje duhova s odredenom oznakom.
            var bs = [4, 4, 4]; //Smijerovi kretanja "bijelih" (pojedenih) duhova.
            var bx = []; //x-koordinata "bijelih" duhova.
            var by = []; //y-koordinata "bijelih" duhova.
            var ciljX = 7; //x-koordinata cilja kretanja "bijelih" duhova.
            var ciljY = 6; //y-koordinata cilja kretanja "bijelih" duhova.
            var zaslon = document.getElementById("zaslon"); //AST od SVG-a (njegovom manipulacijom odredujemo sto ce se crtati na zaslonu).
            function onButtonDown()
            {
                changed = true;
                if (pocetno[py + 1].charAt(px) != "W")
                    ps = 2;
            }
            function onButtonUp()
            {
                changed = true;
                if (pocetno[py - 1].charAt(px) != "W")
                    ps = 0;
            }
            function onButtonLeft()
            {
                changed = true;
                if (pocetno[py].charAt(px - 1) != "W")
                    ps = 3;
            }
            function onButtonRight()
            {
                changed = true;
                if (pocetno[py].charAt(px + 1) != "W")
                    ps = 1;
            }
            function drawLine(x1, x2, y1, y2) //Za crtanje labirinta, 'W' iz 'pocetno'.
            {
                var crta = document.createElementNS(svgNS, "line");
                crta.setAttribute("x1", x1);
                crta.setAttribute("x2", x2);
                crta.setAttribute("y1", y1);
                crta.setAttribute("y2", y2);
                crta.setAttribute("stroke-width", 3);
                crta.setAttribute("stroke", "lightBlue");
                zaslon.appendChild(crta);
            }
            function drawSmallCircle(x, y, id) //Mala tockica, 'P' iz 'pocetno'.
            {
                var krug = document.createElementNS(svgNS, "circle");
                krug.setAttribute("cx", x);
                krug.setAttribute("cy", y);
                krug.setAttribute("r", 3);
                krug.setAttribute("fill", "lightYellow");
                krug.setAttribute("id", id);
                zaslon.appendChild(krug);
            }
            function drawBigCircle(x, y, id) //Velika tocka, 'B' iz 'pocetno'
            {
                var krug = document.createElementNS(svgNS, "circle");
                krug.setAttribute("cx", x);
                krug.setAttribute("cy", y);
                krug.setAttribute("r", 5);
                krug.setAttribute("fill", "lightGreen");
                krug.setAttribute("id", id);
                zaslon.appendChild(krug);
            }
            function drawGhost(x, y, color, id, transparent) //Duhovi su geometrijski likovi omedeni crtama (dno) i kubicnom Bezierovom krivuljom (vrh).
            {
                var path = document.createElementNS(svgNS, "path");
                path.setAttribute("fill", color);
                var d = "M " + (x - 8) + " " + (y + 8);
                d += "C " + (x - 5) + " " + (y - 16) + " " + (x + 5) + " " + (y - 16) + " " + (x + 8) + " " + (y + 8);
                d += " l -4 -3 l -4 3 l -4 -3 Z";
                path.setAttribute("d", d);
                path.setAttribute("id", id);
                if (transparent)
                    path.setAttribute("fill-opacity",0.5); //"Bijeli" duhovi.
                zaslon.appendChild(path);
            }
            function drawGhosts()
            {
                for (var i = 0; i < 3; i++) {
                    if (removed[i] && brojac - blue < 30)
                    {
                        drawGhost(bx[i]*20+10,by[i]*20+10,"white","bijeli"+(i+1),true);
                        document.getElementById("bijeli"+(i+1)).
                            setAttribute("transform","translate(0 0)");
                    }
                    else
                    {
                        drawGhost(gx[i] * 20 + 10, gy[i] * 20 + 10,
                            (brojac - blue > 30) ? (colors[i]) : ("blue"), "duh" + (i + 1));
                        document.getElementById("duh" + (i + 1))
                            .setAttribute("transform", "translate(0 0)");
                    }
                }
            }
            function drawPacMan() //PacMan se sastoji od zutog kruga i crnog trokuta (usta).
            {
                var krug = document.createElementNS(svgNS, "circle");
                krug.setAttribute("cx", px * 20 + 10);
                krug.setAttribute("cy", py * 20 + 10);
                krug.setAttribute("r", 10);
                krug.setAttribute("fill", "yellow");
                krug.setAttribute("id", "PacMan");
                krug.setAttribute("transform", "translate(0 0)");
                zaslon.appendChild(krug);
                var usta = document.createElementNS(svgNS, "polygon");
                usta.setAttribute("points", "0,0 10,-10 10,10");
                usta.setAttribute("fill", "black");
                usta.setAttribute("id", "usta");
                usta.setAttribute("transform", "translate(" + (px * 20 + 10) + " " + (py * 20 + 10) + ")");
                if (!((px + py) % 2) || ps == 4) //Pacman zatvori usta kad se nade na neparnoj dijagonali i kada stoji.
                    usta.setAttribute("transform", usta.getAttribute("transform") + " scale(1 0)");
                usta.setAttribute("transform",
                        usta.getAttribute("transform") + " rotate(" + (90 * ps - 90) + ")");
                zaslon.appendChild(usta);
            }
            function nextLevel()
            {
                level++;
                setTimeout(function(){
                           score += 90 + 10 * level;
                           blue = -100;
                           points = 0;
                           maxp = 0;
                           for (var i = 0; i < 3; i++) {
                                gx[i] = ogx[i];
                                gy[i] = ogy[i];
                           }
                           px = opx;
                           py = opy;
                           ps = 1;
                           gs[0] = 1;
                           gs[1] = 0;
                           gs[2] = 3;
                           for (var i = 0; i < 19; i++)
                                for (var j = 0; j < 15; j++)
                                {
                                    if (pocetno[i].charAt(j) == "P")
                                    {
                                        drawSmallCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                                        maxp++;
                                    }
                                    if (pocetno[i].charAt(j) == "B")
                                    {
                                        drawBigCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                                        maxp++;
                                    }
                                }
                           }
                           ,1950); //Novi level se postavlja tek kada natpis o novom levelu nestane (nakon 1950 milisekunda).
                showLevel();
            }
            function touchScreenInterface(event) //Gdje se nalazi polje u labirintu koje je korisnik dotaknuo? Je li, na primjer, vise lijevo ili vise prema gore od PacMana?
            {
                var x = Math.floor((event.clientX-(document.body.clientWidth/2-300/2)) / 20);
                var y = Math.floor(event.clientY / 20);
                if (Math.abs(px - x) > Math.abs(py - y))
                {
                    if (x < px)
                        onButtonLeft();
                    else
                        onButtonRight();
                } else
                {
                    if (y < py)
                        onButtonUp();
                    else
                        onButtonDown();
                }

            }
            function mainLoop() //Glavna petlja, prati u kojem se polju iz 'pocetno' trenutno nalaze PacMan i duhovi.
            {
                changed = false;
                brojac++;
                brojac1 = 0;
                var pacman = zaslon.getElementById("PacMan");
                if (pacman != null) { //PacMan se crta svaki put kada se ude u 'mainLoop'.
                    zaslon.removeChild(pacman);
                    zaslon.removeChild(zaslon.getElementById("usta"));
                    zaslon.removeChild(zaslon.getElementById("TouchScreenInterface"));
                }
                if (pocetno[py + sy[ps]].charAt(px + sx[ps]) == "W") //Ako se je PacMan nabio u zid, mora stati.
                    ps = 4;
                for (var i = 0; i < 3; i++) {
                    var duh = zaslon.getElementById("duh" + (i + 1));
                    if (duh != null) //Duhovi se crtaju svaki put iznova kada se ude u 'mainLoop'.
                        zaslon.removeChild(duh);
                    var bijeli = zaslon.getElementById("bijeli"+(i+1));
                    if (bijeli!=null)
                        zaslon.removeChild(bijeli);
                    var brojac2 = 0;
                    for (var j = 0; j < 4; j++)
                        if (pocetno[gy[i] + sy[j]].charAt(gx[i] + sx[j]) != "W") //Nalazi li se duh na krizistu hodnika iz labirinta?
                            brojac2++;
                    var suprotniSmjer = (gs[i] + 2) % 4;
                    if (pocetno[gy[i] + sy[gs[i]]].charAt(gx[i] + sx[gs[i]]) == "W" || brojac2 > 2) {
                        if (Math.abs(px - gx[i]) > Math.abs(py - gy[i]) //Ako je PacMan vise udaljen od duha u smijeru lijevo-desno.
                                && !((gy[i] == 7 || gy[i] == 8) && gx[i] > 4 && gx[i] < 9) /*Ako duh nije u "kucici" u sredini labirinta (gdje je bio na pocetku nivoa).*/)
                        {
                            //Crveni duh od treceg nivoa nadalje pokusava pratiti PacMana.
                            if (px < gx[i] && i == 0 && pocetno[gy[i]].charAt(gx[i] - 1) != "W"
                                    && level > 1) {
                                gs[i] = 3; //Ako je PacMan lijevo, usmjeri crvenog duha lijevo.
                                continue;
                            }
                            if (px > gx[i] && i == 0 && pocetno[gy[i]].charAt(gx[i] + 1) != "W"
                                    && level > 1) {
                                gs[i] = 1; //Ako je PacMan desno, usmjeri crvenog duha desno.
                                continue;
                            }
                            if (py < gy[i] && i == 0 && pocetno[gy[i] - 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level > 1) {
                                gs[i] = 0; //Ako je PacMan gore, usmjeri crvenog duha gore.
                                continue;
                            }
                            if (py > gy[i] && i == 0 && pocetno[gy[i] + 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level > 1) {
                                gs[i] = 2; //Ako je PacMan dolje, usmjeri crvenog duha dolje.
                                continue;
                            }
                            //Narancasti duh od drugog nivoa nadalje "bjezi" od PacMana.
                            if (px < gx[i] && i == 2 && pocetno[gy[i]].charAt(gx[i] + 1) != "W"
                                    && level) {
                                gs[i] = 1; //Ako je PacMan lijevo, usmjeri narancastog duha desno.
                                continue;
                            }
                            if (px > gx[i] && i == 2 && pocetno[gy[i]].charAt(gx[i] - 1) != "W"
                                    && level) {
                                gs[i] = 3; //Ako je PacMan desno, usmjeri narancastog duha lijevo.
                                continue;
                            }
                            if (py < gy[i] && i == 2 && pocetno[gy[i] + 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level) {
                                gs[i] = 2; //Ako je PacMan gore, usmjeri narancastog duha dolje.
                                continue;
                            }
                            if (py > gy[i] && i == 2 && pocetno[gy[i] - 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level) {
                                gs[i] = 0; //Ako je PacMan dolje, usmjeri narancastog duha gore.
                                continue;
                            }
                        } else if (!((gy[i] == 7 || gy[i] == 8) && gx[i] > 4 && gx[i] < 9)) //Ako je PacMan vise udaljen od duha u smijeru gore-dolje, a duh nije u "kucici" u sredini labirinta.
                        {
                            if (py < gy[i] && i == 0 && pocetno[gy[i] - 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level > 1) {
                                gs[i] = 0; //Crveni prema gore
                                continue;
                            }
                            if (py > gy[i] && i == 0 && pocetno[gy[i] + 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level > 1) {
                                gs[i] = 2; //Crveni prema dolje
                                continue;
                            }
                            if (px < gx[i] && i == 0 && pocetno[gy[i]].charAt(gx[i] - 1) != "W"
                                    && level > 1) {
                                gs[i] = 3; //Crveni prema lijevo
                                continue;
                            }
                            if (px > gx[i] && i == 0 && pocetno[gy[i]].charAt(gx[i] + 1) != "W"
                                    && level > 1) {
                                gs[i] = 1; //Crveni prema desno
                                continue;
                            }
                            if (py < gy[i] && i == 2 && pocetno[gy[i] + 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level) {
                                gs[i] = 2; //Narancasti prema dolje
                                continue;
                            }
                            if (py > gy[i] && i == 2 && pocetno[gy[i] - 1].charAt(gx[i]) != "W"
                                    && !(gx[i] == 7 && gy[i] == 6) && level) {
                                gs[i] = 0; //Narancasti prema gore
                                continue;
                            }
                            if (px < gx[i] && i == 2 && pocetno[gy[i]].charAt(gx[i] + 1) != "W"
                                    && level) {
                                gs[i] = 1; //Narancasti prema desno
                                continue;
                            }
                            if (px > gx[i] && i == 2 && pocetno[gy[i]].charAt(gx[i] - 1) != "W"
                                    && level) {
                                gs[i] = 3; //Narancasti prema lijevo
                                continue;
                            }
                        }
                        do
                        {
                            gs[i] = Math.floor(Math.random() * 4); //Ruzicasti duh, svi duhovi u kucici, te crveni i narancasti duh prije treceg ili drugog nivoa se gibaju nasumicno.
                        } while (pocetno[gy[i] + sy[gs[i]]].charAt(gx[i] + sx[gs[i]]) == "W"
                                || (gs[i] == suprotniSmjer && ((gx[i]!=ciljX-1 && gx[i]!=ciljX+1) || gy[i]!=ciljY+2))); //Nije pozeljno da duh na krizistu putova krene u suprotnom smijeru no sto je prije isao.                   
                    }
                        if (Math.abs(gx[i] - px) < 2 && Math.abs(gy[i] - py) < 2 && brojac - blue > 30) { //Ako se duh i PacMan sudare, a od posljednjeg konzumiranja velike tocke je proslo vise od 30 sekundi.
                        if (lives) {
                            zaslon.removeChild(zaslon.getElementById("live" + (lives)));
                            lives--;
                            for (var i = 0; i < 3; i++) {
                                gx[i] = ogx[i];
                                gy[i] = ogy[i];
                            }
                            px = opx;
                            py = opy;
                            ps = 1;
                            gs[0] = 1;
                            gs[1] = 0;
                            gs[2] = 3;
                            return;
                        } else //Ako vise nema zivota.
                        {
                            if (finished)
                                return; //Ako se ovaj blok pozove dvaput.
                            else
                                finished=true;
                            alert("Game over! Your score: " + score + ". Hope you enjoyed. Author: Teo Samarzija.");
                            clearInterval(time1); //Prestani pratiti gdje se nalazi PacMan, a gdje duhovi.
                            clearInterval(time2); //Zaustavi animacije.
                            if (score>(highscore)*1)
                            {
                                var player;
                                do {
                                    player=window.prompt("Enter your name, new highscore! Your name mustn't contain whitespaces or special characters.","player");
                                }
                                while (player && (player.indexOf("<")+1 || player.indexOf(">")+1 || player.indexOf("&")+1 || player.indexOf(" ")+1));
                                if (player==null)
                                    player="anonymous";
                                var hash=7;
                                for (var i=0; i<score/127; i++)
                                {
                                    hash+=i;
                                    hash%=907;
                                }
                                var submit="http://flatassembler.000webhostapp.com/pacHigh.php?score="+score+"&player="+player+"&hash="+hash;
                                var link=document.createElement("a");
                                link.setAttribute("href",submit);
                                link.appendChild(document.createTextNode("Submit the new highscore!"));
                                link.setAttribute("target","_blank"); //Otvori u novom prozoru, da se moze zatvoriti iz JavaScripta.
                                document.getElementById("bodovi").appendChild(document.createElement("br"));
                                document.getElementById("bodovi").appendChild(link);
                            }
                            else window.close();
                        }
                    }
                    if (Math.abs(gx[i] - px) < 2 && Math.abs(gy[i] - py) < 2 && brojac - blue < 30) //Ako PacMan pojede "plavog" duha.
                    {
                        bx[i]=gx[i];
                        by[i]=gy[i];
                        score += 10 + 2 * level;
                        removed[i] = true;
                        gx[i] = ogx[i];
                        gy[i] = ogy[i];
                        continue;
                    }
                }
                for (var i=0; i<3; i++)
                {
                    if (bx[i]==ciljX && (by[i]==ciljY || by[i]==ciljY+1)) //Ako je bijeli duh pred vratima...
                        {bs[i]=2; continue;} //... neka ude u kucicu.
                    if (bx[i]==ciljX && by[i]==ciljY+2 && bs[i]==2) //Ako je bijeli duh upravo usao u kucicu...
                        {bs[i]=3; continue;} //... neka ide lijevo.
                    if (bx[i]==ciljX && by[i]==ciljY+2) //Ako je bijeli duh na sredini kucice...
                        continue; //Neka zadrzi smijer.
                    if (bx[i]==ciljX-1 && by[i]==ciljY+2) //Ako je bijeli duh na lijevom zidu kucice
                        {bs[i]=1; continue} //... neka ide desno.
                    if (bx[i]==ciljX+1 && by[i]==ciljY+2) //Ako je bijeli duh na desnom zidu kucice...
                        {bs[i]=3; continue;} //... neka ide lijevo.
                    if ((Math.abs(bx[i]-ciljX)==1 || Math.abs(bx[i]-ciljX)==2) &&
                        pocetno[by[i]+sy[bs[i]]].charAt(bx[i]+sx[bs[i]])!='W' && bs[i]-4)
                        continue; //Greedy algoritam za odabiranje smijera ne funkcionira u nekim slucajevima.
                    if (by[i]>ciljY && pocetno[by[i]-1].charAt(bx[i])!='W') //Ako je bijeli duh ispod cilja...
                            {bs[i]=0; continue;} //... usmjeri ga prema gore.
                    if (by[i]<ciljY && pocetno[by[i]+1].charAt(bx[i])!='W') //Ako je bijeli duh iznad cilja...
                            {bs[i]=2; continue;} //... usmjeri ga prema dolje.
                    if (bx[i]>ciljX && pocetno[by[i]].charAt(bx[i]-1)!='W') //Ako je bijeli duh desno od cilja...
                            {bs[i]=3; continue;} //... usmjeri ga prema lijevo.
                    if (bx[i]<ciljX && pocetno[by[i]].charAt(bx[i]+1)!='W') //Ako je bijeli duh lijevo od cilja...
                            {bs[i]=1; continue;} //... usmjeri ga prema desno.
                    if (bx[i]==ciljX && by[i]>ciljY && pocetno[by[i]-1].charAt(bx[i])=='W') //Ako je bijeli duh ravno ispod cilja, a iznad njega zid...
                            {bs[i]=3; continue;} //... usmjeri ga prema lijevo.
                }
                if (zaslon.getElementById("krug" + (py * 20 + px)) != null) { //Ako pojede tockicu.
                    zaslon.removeChild(zaslon.getElementById("krug" + (py * 20 + px)));
                    if (pocetno[py].charAt(px) == "B") //Ako je upravo pojedena velika tocka.
                    {
                        blue = brojac;
                        score += 4 + level;
                        removed = [false, false, false];
                    }
                    score += 1 + level;
                    points++;
                    var bodovi = document.getElementById("score");
                    bodovi.removeChild(bodovi.lastChild);
                    bodovi.appendChild(document.createTextNode("Score: " + score));
                }
                drawGhosts();
                drawPacMan();
                var touch = document.createElementNS(svgNS, "rect"); //Prozirni pravokutnik preko labirinta prima evente kada korsnik dodirne negdje u labirint.
                touch.setAttribute("x", 0);
                touch.setAttribute("y", 20);
                touch.setAttribute("width", 300);
                touch.setAttribute("height", 340);
                touch.setAttribute("id", "TouchScreenInterface");
                touch.setAttribute("fill-opacity", 0);
                touch.addEventListener("click", touchScreenInterface);
                zaslon.appendChild(touch);
                for (var i = 0; i < 3; i++)
                {
                    if (removed[i] && brojac - blue < 30)
                    {
                        bx[i]+=sx[bs[i]];
                        by[i]+=sy[bs[i]];
                        continue;
                    }
                    gx[i] += sx[gs[i]];
                    gy[i] += sy[gs[i]];
                    if (gx[i] > 14) //Ako duh prode kroz prolaz desno u sredini labirinta.
                        gx[i] = 0;
                    if (gx[i] < 0) //Ako duh prode kroz prolaz lijevo u sredini labirinta.
                        gx[i] = 14;
                }
                px += sx[ps];
                py += sy[ps];
                if (px > 14) //Ako PacMan prode kroz desni prolaz.
                    px = 0;
                if (px < 0) //Ako PacMan prode kroz lijevi prolaz.
                    px = 14;
                if (points == maxp) //Ako je PacMan pojeo sve tockice.
                    nextLevel();
            }
            function animationLoop()
            {
                if (brojac < 2)
                    return; //Ne pokusavaj animirati PacMana i duhove ako jos nisu nacrtani.
                brojac1++;
                for (var i = 0; i < 3; i++) {
                    if (removed[i] && brojac - blue < 30) //Ako je PacMan nedavno pojeo duha, animiraj bijelu siluetu...
                     zaslon.getElementById("bijeli" + (i + 1)).setAttribute("transform",
                            "translate(" + (20 / 5) * brojac1 * sx[bs[i]] + " " + (20 / 5) * brojac1 * sy[bs[i]] + ")");
                    else //... inace animiraj duha.   
                        zaslon.getElementById("duh" + (i + 1)).setAttribute("transform",
                            "translate(" + (20 / 5) * brojac1 * sx[gs[i]] + " " + (20 / 5) * brojac1 * sy[gs[i]] + ")");
                }
                if (changed == true) //Nemoj animirati PacMana ukoliko on upravo mijenja smijer.
                    return;
                zaslon.getElementById("PacMan").setAttribute("transform",
                        "translate(" + (20 / 5) * brojac1 * sx[ps] + " " + (20 / 5) * brojac1 * sy[ps] + ")");
                var usta = zaslon.getElementById("usta");
                usta.setAttribute("transform",
                        "translate(" + ((20 / 5) * brojac1 * sx[ps] + ((px - sx[ps]) * 20 + 10))
                        + " " + ((20 / 5) * brojac1 * sy[ps] + (py - sy[ps]) * 20 + 10) + ")");
                if (!((px + py) % 2) /*Na poljima na parnim dijagonalama ce usta biti zatvorena, a na neparnima otvorena.*/ && (ps == 1 || ps == 3))
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(1 " + (brojac1 * 0.2) + ")");
                else if (ps == 1 || ps == 3)
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(1 " + (1 - brojac1 * 0.2) + ")");
                else if (!((px + py) % 2) && (ps == 2 || !ps))
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(" + (brojac1 * 0.2) + " 1)");
                else if (ps == 2 || !ps)
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(" + (1 - brojac1 * 0.2) + " 1)");
                else if (ps == 4) //PacMan, ako se ne mice, uvijek drzi usta zatvorenima.
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(1 0)");
                usta.setAttribute("transform",
                        usta.getAttribute("transform") + " rotate(" + (90 * ps - 90) + ")");
            }
            //Crtanje labirinta na pocetku igre.
            for (var i = 0; i < 19; i++)
                for (var j = 0; j < 15; j++)
                {
                    if (pocetno[i].charAt(j) == "W")
                    {
                        if (pocetno[i - 1].charAt(j) == "W")
                            drawLine(j * 20 + 10, j * 20 + 10, i * 20, i * 20 + 10);
                        if (pocetno[i + 1].charAt(j) == "W")
                            drawLine(j * 20 + 10, j * 20 + 10, i * 20 + 10, i * 20 + 20);
                        if (pocetno[i].charAt(j - 1) == "W")
                            drawLine(j * 20, j * 20 + 10, i * 20 + 10, i * 20 + 10);
                        if (pocetno[i].charAt(j + 1) == "W")
                            drawLine(j * 20 + 10, j * 20 + 20, i * 20 + 10, i * 20 + 10);
                    }
                    if (pocetno[i].charAt(j) == "P")
                    {
                        drawSmallCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                        maxp++;
                    }
                    if (pocetno[i].charAt(j) == "B")
                    {
                        drawBigCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                        maxp++;
                    }
                    if (pocetno[i].charAt(j) == "C")
                    {
                        px = opx = j;
                        py = opy = i;
                    }
                    if (pocetno[i].charAt(j) > "0" && pocetno[i].charAt(j) < "4") //Duhovi.
                    {
                        //charCodeAt - vraca ASCII vrijednost znaka iz stringa (broj), to je vazno zbog arraysova, arr["0"] ne znaci isto sto i arr[0].
                        gx[pocetno[i].charCodeAt(j) - "1".charCodeAt(0)] = j;
                        gy[pocetno[i].charCodeAt(j) - "1".charCodeAt(0)] = i;
                        ogy[pocetno[i].charCodeAt(j) - "1".charCodeAt(0)] = i;
                        ogx[pocetno[i].charCodeAt(j) - "1".charCodeAt(0)] = j;

                    }
                }
            //Crtanje PacMana u lijevom donjem kutu koji oznacaju preostale zivote.
            for (var i = 0; i < lives; i++)
            {
                var krug = document.createElementNS(svgNS, "circle");
                krug.setAttribute("fill", "yellow");
                krug.setAttribute("cx", 25 + i * 25);
                krug.setAttribute("cy", 380);
                krug.setAttribute("r", 10);
                krug.setAttribute("id", "live" + (i + 1));
                zaslon.appendChild(krug);
                var usta = document.createElementNS(svgNS, "polygon");
                usta.setAttribute("points",
                        (25 + i * 25) + ",380 " + (35 + i * 25) + ",370 " + (35 + i * 25) + " 390");
                usta.setAttribute("fill", "black");
                zaslon.appendChild(usta);
            }
            drawGhosts();
            drawPacMan();
            function onStartButton()
            {
                zaslon.removeChild(zaslon.getElementById("startButton"));
                zaslon.removeChild(zaslon.getElementById("startText"));
                showLevel(); //U funkciji "showlevel" se postavlja timer.
            }
            function nestajanje() //Natpis o tome na kojem smo levelu ne iscezava odjednom, nego postupno.
            {
                var natpis=document.getElementById("natpis");
                if (brojac2<16)
                {
                    brojac2++;
                    natpis.style.opacity-=1/15;
                    natpis.style.left=(document.body.clientWidth/2-300/2+50+brojac2)+"px"; //Kako natpis nestaje, polako se pomice udesno.
                    setTimeout(nestajanje,100);
                }
                else
                    document.body.removeChild(natpis);
            }
            function showLevel()
            {
                clearInterval(time1);
                clearInterval(time2);
                //Pacman i duhovi se ne smiju pomicati iza natpisa da smo presli na novi level.
                var natpis=document.createElement("div");
                natpis.setAttribute("style","position:absolute;background-color:#AAFFFF;color:red;top:180px;width:200px;height:50px;border-radius:100%;text-align:center;font-family:Arial;font-size:24px;line-height:50px;"); //Ovako se mogu pozivati (iako to nije preporucljivo) CSS naredbe iz JavaScript programa. "border-radius:100%" znaci da natpis bude u obliku elipse.
                natpis.style.opacity=1.0;
                natpis.style.left=document.body.clientWidth/2-300/2+50;
                natpis.innerHTML="<b>LEVEL #"+(level+1)+"<\/b>"; //Ovako se mogu pozivati naredbe iz HTML-a u JavaScript programu.
                natpis.id="natpis";
                document.body.appendChild(natpis);
                brojac2=0;
                setTimeout(nestajanje,500); //Neka natpis o tome na kojem smo levelu pocne iscezavati nakon 500 milisekundi.
                setTimeout(function(){
                            time1 = window.setInterval(mainLoop, 500);
                            time2 = window.setInterval(animationLoop, 100);
                           },2000); //Neka se glavna i animacijska petlja pocnu vrtiti nakon 2000 milisekunda od trenutka kada prijedemo na novi level.
            }
        </script>
    </body>
</html>
