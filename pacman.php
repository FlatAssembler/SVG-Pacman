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
        <style type="text/css">
        #natpis { /*CSS od natpisa za novi level.*/
            position:absolute;
            background-color:#AAFFFF;
            color:red;
            top:180px;
            width:200px;
            height:50px;
            border-radius:100%; /*Neka bude u obliku elipse.*/
            text-align:center;
            font-family:Arial;
            font-size:24px;
            line-height:50px;
        }
        #startButton {
            background-color:red;
            top:275px;
            color:yellow;
            font-size:36px;
            width:200px;
            position:absolute;
            left:-webkit-calc(50% - (200px / 2)); /*Za Android Stock Browser 4 i Safari 6, oni nece parsirati "calc" ako ne stavimo prefiks.*/
            left:calc(50% - (200px / 2));
        }
        #zaslon {
            background:black;
            display:block;
            width: 300px;
            height: 450px;
            border:0px;
            margin-bottom: 0px;
            overflow: hidden; /*Inace ce u Internet Exploreru 11 duhovi doci na bijelu pozadinu kad prolaze kroz onaj prolaz sa strane.*/
        }
        #bodovi { /*Zuti pravokutnik na kojem pise highscore.*/
            position: absolute;
            top:458px;
            width:300px;
            line-height:50px;
            font-family: Lucida;
            background-color: yellow;
            text-align:center;
            margin-bottom:5px;
            margin-top:0px;
            left:-webkit-calc(50% - (300px / 2));
            left:calc(50% - (300px / 2)); 
        }
        #instructions {
            position: absolute;
            top:-webkit-calc(458px + 50px + 5px);
            top:calc(458px + 50px + 5px);
            width:-webkit-calc(100% - 2 * 8px);
            width:calc(100% - 2 * 8px);
        }
        </style>
    </head>
    <body>
        <?php
        $datoteka=fopen("pachigh.txt","r");
        $highscore=intval(fgets($datoteka));
        $player=fgets($datoteka);
        fclose($datoteka);
        if (strpos($player," ") || strpos($player,"<") || strpos($player,">") ||
            strpos($player,"&") || strpos($player,"\t") || strpos($player,"&gt;")
            || strlen($player)==0)
            $player="anonymous";
        ?>
		<button id="startButton" onclick="onStartButton()">START!</button>
        <center>
        <svg id="zaslon">
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
        </svg>
		<br>
<div id="bodovi" style="">Highscore: <i><?php echo $highscore; ?></i> by <i><?php echo $player; ?></i>.</div>
<div id="instructions">The game does NOT respond to keyboard buttons. On smartphones, the Pacman is supposed to follow your finger, to go in the direction where you last tapped. In case that doesn't work, you have buttons below the maze. On computers, it's playable by mouse.<br/>You can see the source code, with the comments in Croatian, <a href="https://github.com/FlatAssembler/SVG-Pacman/blob/master/pacman.php">here</a>.</div>
        </center>
        <script type="text/javascript">
            window.setTimeout(function(){document.body.removeChild(document.body.children[document.body.children.length-1]);},1000); //Ukloni "Powered by 000webhost", da ne smeta na smartphonima.
            var isGameFinished=false;
            var highscore=<?php echo $highscore; ?>; //Ovaj podatak u JavaScript kod umece PHP program koji se vrti na serveru.
            var kolikoJePacmanuPreostaloZivota = 3, time1 = 0, time2 = 0, score = 0, kadaJePacmanPojeoVelikuTocku = -100 /*"Plavi" duhovi se mogu "pojesti". Duhovi prestanu biti plavi nakon sto prode odredeno vrijeme (koje se broji varijablom 'brojacGlavnePetlje').*/
            var howManyDotsAreThere = 0 /*Broj tocaka u svakom nivou, PacMan ih mora pojesti sve da prijede na iduci nivo.*/
            var howManyDotsHasPacmanEaten = 0;
            var level = 0;
            var XML_namespace_of_SVG = "http://www.w3.org/2000/svg"; //Ovo je potrebno jer SVG ne koristi HTML DOM, nego XML DOM s drugim namespaceom.
            var brojacGlavnePetlje = 0, brojacAnimacijskePetlje = 0, kolikoJePutaDuhPromijenioSmjer=0;
            var hasPacmanChangedDirection = false; //Je li za vrijeme izvrsavanja animacijske petlje promijenjen smjer kretanja PacMan-a.
            var pocetnoStanjeLabirinta = new Array(19);
            /*
             W - zid
             B - velika tockica (koja, kada ju PacMan pojede, ucini da duhovi poplave).
             P - mala tockica (donosi bodove, te, ako ih PacMan sve pojede, prelazi na iduci level).
             C - PacMan (gdje se nalazi na pocetku nivoa)
             1 - crveni duh
             2 - ruzicasti duh
             3 - narancasti duh
             */
            pocetnoStanjeLabirinta[ 0] = "               ";
            pocetnoStanjeLabirinta[ 1] = " WWWWWWWWWWWWW ";
            pocetnoStanjeLabirinta[ 2] = " WBPPPPWPPPPBW ";
            pocetnoStanjeLabirinta[ 3] = " WPWWWPWPWWWPW ";
            pocetnoStanjeLabirinta[ 4] = " WPW WPWPW WPW ";
            pocetnoStanjeLabirinta[ 5] = " WPWWWPWPWWWPW ";
            pocetnoStanjeLabirinta[ 6] = " WPPPPP PPPPPW ";
            pocetnoStanjeLabirinta[ 7] = "WWWWPWW WWPWWWW";
            pocetnoStanjeLabirinta[ 8] = "    PW123WP    ";
            pocetnoStanjeLabirinta[ 9] = "WWWWPWWWWWPWWWW";
            pocetnoStanjeLabirinta[10] = "   WPP C PPW   ";
            pocetnoStanjeLabirinta[11] = " WWWPWWWWWPWWW ";
            pocetnoStanjeLabirinta[12] = " WPPPPPPPPPPPW ";
            pocetnoStanjeLabirinta[13] = " WPWWWWPWWWWPW ";
            pocetnoStanjeLabirinta[14] = " WPW  WPW  WPW ";
            pocetnoStanjeLabirinta[15] = " WPWWWWPWWWWPW ";
            pocetnoStanjeLabirinta[16] = " WPPPPPBPPPPPW ";
            pocetnoStanjeLabirinta[17] = " WWWWWWWWWWWWW ";
            pocetnoStanjeLabirinta[18] = "               ";
            //Smjerovi kretanja. Smjer '0' znaci 'gore', smjer '1' znaci 'desno', smjer '2' znaci 'dolje', smjer '3' znaci 'lijevo', dok smjer '4' znaci 'stoji'.
            var xKomponentaSmjeraPacmana = [0, 1, 0, -1, 0];
            var yKomponentaSmjeraPacmana = [-1, 0, 1, 0, 0];
            //Smjerovi kretanja duhova i pacmana. Crveni ce se na pocetku nivoa kretati desno, ruzicasti gore, a narancasti lijevo. Pacman ce se kretati desno.
            var smjerDuha = [1, 0, 3], smjerPacmana = 1;
            var pocetnaXKoordinataPacmana, pocetnaYKoordinataPacmana, pocetnaXKoordinataDuha = [], pocetnaYKoordinataDuha = [];
            var xKoordinataPacmana = 0;
            var yKoordinataPacmana = 0;
            var xKoordinataDuha = new Array(3);
            var yKoordinataDuha = new Array(3);
            var jeLiPacmanPojeoDuha = [false, false, false];
            var bojaDuha = ["red", "pink", "orange"];
            var smjerKretanjaSiluete = [4, 4, 4];
            var xKoordinataSiluete = new Array(3);
            var yKoordinataSiluete = new Array(3);
            var xKoordinataCiljaSiluete = 7;
            var yKoordinataCiljaSiluete = 6;
            var zaslon = document.getElementById("zaslon"); //AST od SVG-a (njegovom manipulacijom odredujemo sto ce se crtati na zaslonu).
            function onButtonDown()
            {
                hasPacmanChangedDirection = true;
                if (pocetnoStanjeLabirinta[yKoordinataPacmana + 1].charAt(xKoordinataPacmana) != "W")
                    smjerPacmana = 2;
            }
            function onButtonUp()
            {
                hasPacmanChangedDirection = true;
                if (pocetnoStanjeLabirinta[yKoordinataPacmana - 1].charAt(xKoordinataPacmana) != "W")
                    smjerPacmana = 0;
            }
            function onButtonLeft()
            {
                hasPacmanChangedDirection = true;
                if (pocetnoStanjeLabirinta[yKoordinataPacmana].charAt(xKoordinataPacmana - 1) != "W")
                    smjerPacmana = 3;
            }
            function onButtonRight()
            {
                hasPacmanChangedDirection = true;
                if (pocetnoStanjeLabirinta[yKoordinataPacmana].charAt(xKoordinataPacmana + 1) != "W")
                    smjerPacmana = 1;
            }
            function drawLine(x1, x2, y1, y2) //Za crtanje labirinta, 'W' iz 'pocetnoStanjeLabirinta'.
            {
                var crta = document.createElementNS(XML_namespace_of_SVG, "line");
                crta.setAttribute("x1", x1);
                crta.setAttribute("x2", x2);
                crta.setAttribute("y1", y1);
                crta.setAttribute("y2", y2);
                crta.setAttribute("stroke-width", 3);
                crta.setAttribute("stroke", "lightBlue");
                zaslon.appendChild(crta);
            }
            function drawSmallCircle(x, y, id) //Mala tockica, 'P' iz 'pocetnoStanjeLabirinta'.
            {
                var krug = document.createElementNS(XML_namespace_of_SVG, "circle");
                krug.setAttribute("cx", x);
                krug.setAttribute("cy", y);
                krug.setAttribute("r", 3);
                krug.setAttribute("fill", "lightYellow");
                krug.setAttribute("id", id);
                zaslon.appendChild(krug);
            }
            function drawBigCircle(x, y, id) //Velika tocka, 'B' iz 'pocetnoStanjeLabirinta'
            {
                var krug = document.createElementNS(XML_namespace_of_SVG, "circle");
                krug.setAttribute("cx", x);
                krug.setAttribute("cy", y);
                krug.setAttribute("r", 5);
                krug.setAttribute("fill", "lightGreen");
                krug.setAttribute("id", id);
                zaslon.appendChild(krug);
            }
            function drawGhost(x, y, color, id, transparent) //Duhovi su geometrijski likovi omedeni crtama (dno) i kubicnom Bezierovom krivuljom (vrh).
            {
                var path = document.createElementNS(XML_namespace_of_SVG, "path");
                path.setAttribute("fill", color);
                var d = "M " + (x - 8) + " " + (y + 8);
                d += "C " + (x - 5) + " " + (y - 16) + " " + (x + 5) + " " + (y - 16) + " " + (x + 8) + " " + (y + 8);
                d += " l -4 -3 l -4 3 l -4 -3 Z";
                path.setAttribute("d", d);
                path.setAttribute("id", id);
                if (transparent)
                    path.setAttribute("fill-opacity",0.5); //Siluete (bijeli duhovi).
                zaslon.appendChild(path);
            }
            function drawGhosts()
            {
                for (var i = 0; i < 3; i++) {
                    if (jeLiPacmanPojeoDuha[i] && brojacGlavnePetlje - kadaJePacmanPojeoVelikuTocku < 30)
                    {
                        drawGhost(xKoordinataSiluete[i]*20+10,yKoordinataSiluete[i]*20+10,"white","bijeli"+(i+1),true);
                        document.getElementById("bijeli"+(i+1)).
                            setAttribute("transform","translate(0 0)");
                    }
                    else
                    {
                        drawGhost(xKoordinataDuha[i] * 20 + 10, yKoordinataDuha[i] * 20 + 10,
                            (brojacGlavnePetlje - kadaJePacmanPojeoVelikuTocku > 30) ? (bojaDuha[i]) : ("blue"), "duh" + (i + 1));
                        document.getElementById("duh" + (i + 1))
                            .setAttribute("transform", "translate(0 0)");
                    }
                }
            }
            function drawPacMan() //PacMan se sastoji od zutog kruga i crnog trokuta (usta).
            {
                var krug = document.createElementNS(XML_namespace_of_SVG, "circle");
                krug.setAttribute("cx", xKoordinataPacmana * 20 + 10);
                krug.setAttribute("cy", yKoordinataPacmana * 20 + 10);
                krug.setAttribute("r", 10);
                krug.setAttribute("fill", "yellow");
                krug.setAttribute("id", "PacMan");
                krug.setAttribute("transform", "translate(0 0)");
                zaslon.appendChild(krug);
                var usta = document.createElementNS(XML_namespace_of_SVG, "polygon");
                usta.setAttribute("points", "0,0 10,-10 10,10");
                usta.setAttribute("fill", "black");
                usta.setAttribute("id", "usta");
                usta.setAttribute("transform", "translate(" + (xKoordinataPacmana * 20 + 10) + " " + (yKoordinataPacmana * 20 + 10) + ")");
                if (!((xKoordinataPacmana + yKoordinataPacmana) % 2) || smjerPacmana == 4) //Pacman zatvori usta kad se nade na neparnoj dijagonali i kada stoji.
                    usta.setAttribute("transform", usta.getAttribute("transform") + " scale(1 0)");
                usta.setAttribute("transform",
                        usta.getAttribute("transform") + " rotate(" + (90 * smjerPacmana - 90) + ")");
                zaslon.appendChild(usta);
            }
            function nextLevel()
            {
                level++;
                setTimeout(function(){
                           score += 90 + 10 * level;
                           kadaJePacmanPojeoVelikuTocku = -100;
                           howManyDotsHasPacmanEaten = 0;
                           howManyDotsAreThere = 0;
                           for (var i = 0; i < 3; i++) {
                                xKoordinataDuha[i] = pocetnaXKoordinataDuha[i];
                                yKoordinataDuha[i] = pocetnaYKoordinataDuha[i];
                           }
                           xKoordinataPacmana = pocetnaXKoordinataPacmana;
                           yKoordinataPacmana = pocetnaYKoordinataPacmana;
                           smjerPacmana = 1;
                           smjerDuha[0] = 1;
                           smjerDuha[1] = 0;
                           smjerDuha[2] = 3;
                           for (var i = 0; i < 19; i++)
                                for (var j = 0; j < 15; j++)
                                {
                                    if (pocetnoStanjeLabirinta[i].charAt(j) == "P")
                                    {
                                        drawSmallCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                                        howManyDotsAreThere++;
                                    }
                                    if (pocetnoStanjeLabirinta[i].charAt(j) == "B")
                                    {
                                        drawBigCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                                        howManyDotsAreThere++;
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
                if (Math.abs(xKoordinataPacmana - x) > Math.abs(yKoordinataPacmana - y))
                {
                    if (x < xKoordinataPacmana)
                        onButtonLeft();
                    else
                        onButtonRight();
                } else
                {
                    if (y < yKoordinataPacmana)
                        onButtonUp();
                    else
                        onButtonDown();
                }

            }
            function mainLoop() //Glavna petlja, prati u kojem se polju iz 'pocetnoStanjeLabirinta' trenutno nalaze PacMan i duhovi.
            {
                hasPacmanChangedDirection = false;
                brojacGlavnePetlje++;
                brojacAnimacijskePetlje = 0;
                var pacman = zaslon.getElementById("PacMan");
                if (pacman != null) { //PacMan se crta svaki put kada se ude u 'mainLoop'.
                    zaslon.removeChild(pacman);
                    zaslon.removeChild(zaslon.getElementById("usta"));
                    zaslon.removeChild(zaslon.getElementById("TouchScreenInterface"));
                }
                if (pocetnoStanjeLabirinta[yKoordinataPacmana + yKomponentaSmjeraPacmana[smjerPacmana]].charAt(xKoordinataPacmana + xKomponentaSmjeraPacmana[smjerPacmana]) == "W") //Ako se PacMan nabio u zid, mora stati.
                    smjerPacmana = 4;
                for (var i = 0; i < 3; i++) {
                    var duh = zaslon.getElementById("duh" + (i + 1));
                    if (duh != null) //Duhovi se crtaju svaki put iznova kada se ude u 'mainLoop'.
                        zaslon.removeChild(duh);
                    var bijeli = zaslon.getElementById("bijeli"+(i+1));
                    if (bijeli!=null)
                        zaslon.removeChild(bijeli);
                    var kolikoJePutaDuhPromijenioSmjer = 0;
                    for (var j = 0; j < 4; j++)
                        if (pocetnoStanjeLabirinta[yKoordinataDuha[i] + yKomponentaSmjeraPacmana[j]].charAt(xKoordinataDuha[i] + xKomponentaSmjeraPacmana[j]) != "W") //Nalazi li se duh na krizistu hodnika iz labirinta?
                            kolikoJePutaDuhPromijenioSmjer++;
                    var suprotniSmjer = (smjerDuha[i] + 2) % 4;
                    if (pocetnoStanjeLabirinta[yKoordinataDuha[i] + yKomponentaSmjeraPacmana[smjerDuha[i]]].charAt(xKoordinataDuha[i] + xKomponentaSmjeraPacmana[smjerDuha[i]]) == "W" || kolikoJePutaDuhPromijenioSmjer > 2) {
                        if (Math.abs(xKoordinataPacmana - xKoordinataDuha[i]) > Math.abs(yKoordinataPacmana - yKoordinataDuha[i]) //Ako je PacMan vise udaljen od duha u smjeru lijevo-desno.
                                && !((yKoordinataDuha[i] == 7 || yKoordinataDuha[i] == 8) && xKoordinataDuha[i] > 4 && xKoordinataDuha[i] < 9) /*Ako duh nije u "kucici" u sredini labirinta (gdje je bio na pocetku nivoa).*/)
                        {
                            //Crveni duh od treceg nivoa nadalje pokusava pratiti PacMana.
                            if (xKoordinataPacmana < xKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] - 1) != "W"
                                    && level > 1) {
                                smjerDuha[i] = 3; //Ako je PacMan lijevo, usmjeri crvenog duha lijevo.
                                continue;
                            }
                            if (xKoordinataPacmana > xKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] + 1) != "W"
                                    && level > 1) {
                                smjerDuha[i] = 1; //Ako je PacMan desno, usmjeri crvenog duha desno.
                                continue;
                            }
                            if (yKoordinataPacmana < yKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i] - 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level > 1) {
                                smjerDuha[i] = 0; //Ako je PacMan gore, usmjeri crvenog duha gore.
                                continue;
                            }
                            if (yKoordinataPacmana > yKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i] + 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level > 1) {
                                smjerDuha[i] = 2; //Ako je PacMan dolje, usmjeri crvenog duha dolje.
                                continue;
                            }
                            //Narancasti duh od drugog nivoa nadalje "bjezi" od PacMana.
                            if (xKoordinataPacmana < xKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] + 1) != "W"
                                    && level) {
                                smjerDuha[i] = 1; //Ako je PacMan lijevo, usmjeri narancastog duha desno.
                                continue;
                            }
                            if (xKoordinataPacmana > xKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] - 1) != "W"
                                    && level) {
                                smjerDuha[i] = 3; //Ako je PacMan desno, usmjeri narancastog duha lijevo.
                                continue;
                            }
                            if (yKoordinataPacmana < yKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i] + 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level) {
                                smjerDuha[i] = 2; //Ako je PacMan gore, usmjeri narancastog duha dolje.
                                continue;
                            }
                            if (yKoordinataPacmana > yKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i] - 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level) {
                                smjerDuha[i] = 0; //Ako je PacMan dolje, usmjeri narancastog duha gore.
                                continue;
                            }
                        } else if (!((yKoordinataDuha[i] == 7 || yKoordinataDuha[i] == 8) && xKoordinataDuha[i] > 4 && xKoordinataDuha[i] < 9)) //Ako je PacMan vise udaljen od duha u smjeru gore-dolje, a duh nije u "kucici" u sredini labirinta.
                        {
                            if (yKoordinataPacmana < yKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i] - 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level > 1) {
                                smjerDuha[i] = 0; //Crveni prema gore
                                continue;
                            }
                            if (yKoordinataPacmana > yKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i] + 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level > 1) {
                                smjerDuha[i] = 2; //Crveni prema dolje
                                continue;
                            }
                            if (xKoordinataPacmana < xKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] - 1) != "W"
                                    && level > 1) {
                                smjerDuha[i] = 3; //Crveni prema lijevo
                                continue;
                            }
                            if (xKoordinataPacmana > xKoordinataDuha[i] && i == 0 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] + 1) != "W"
                                    && level > 1) {
                                smjerDuha[i] = 1; //Crveni prema desno
                                continue;
                            }
                            if (yKoordinataPacmana < yKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i] + 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level) {
                                smjerDuha[i] = 2; //Narancasti prema dolje
                                continue;
                            }
                            if (yKoordinataPacmana > yKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i] - 1].charAt(xKoordinataDuha[i]) != "W"
                                    && !(xKoordinataDuha[i] == 7 && yKoordinataDuha[i] == 6) && level) {
                                smjerDuha[i] = 0; //Narancasti prema gore
                                continue;
                            }
                            if (xKoordinataPacmana < xKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] + 1) != "W"
                                    && level) {
                                smjerDuha[i] = 1; //Narancasti prema desno
                                continue;
                            }
                            if (xKoordinataPacmana > xKoordinataDuha[i] && i == 2 && pocetnoStanjeLabirinta[yKoordinataDuha[i]].charAt(xKoordinataDuha[i] - 1) != "W"
                                    && level) {
                                smjerDuha[i] = 3; //Narancasti prema lijevo
                                continue;
                            }
                        }
                        do
                        {
                            smjerDuha[i] = Math.floor(Math.random() * 4); //Ruzicasti duh, svi duhovi u kucici, te crveni i narancasti duh prije treceg ili drugog nivoa gibaju se nasumicno.
                        } while (pocetnoStanjeLabirinta[yKoordinataDuha[i] + yKomponentaSmjeraPacmana[smjerDuha[i]]].charAt(xKoordinataDuha[i] + xKomponentaSmjeraPacmana[smjerDuha[i]]) == "W"
                                || (smjerDuha[i] == suprotniSmjer && ((xKoordinataDuha[i]!=xKoordinataCiljaSiluete-1 && xKoordinataDuha[i]!=xKoordinataCiljaSiluete+1) || yKoordinataDuha[i]!=yKoordinataCiljaSiluete+2))); //Nije pozeljno da duh na krizistu putova krene u suprotnom smjeru no sto je prije isao.                   
                    }
                        if (Math.abs(xKoordinataDuha[i] - xKoordinataPacmana) < 2 && Math.abs(yKoordinataDuha[i] - yKoordinataPacmana) < 2 && brojacGlavnePetlje - kadaJePacmanPojeoVelikuTocku > 30) { //Ako se duh i PacMan sudare, a od posljednjeg konzumiranja velike tocke proslo je vise od 30 sekundi.
                        if (kolikoJePacmanuPreostaloZivota) {
                            zaslon.removeChild(zaslon.getElementById("live" + (kolikoJePacmanuPreostaloZivota)));
                            kolikoJePacmanuPreostaloZivota--;
                            for (var i = 0; i < 3; i++) {
                                xKoordinataDuha[i] = pocetnaXKoordinataDuha[i];
                                yKoordinataDuha[i] = pocetnaYKoordinataDuha[i];
                            }
                            xKoordinataPacmana = pocetnaXKoordinataPacmana;
                            yKoordinataPacmana = pocetnaYKoordinataPacmana;
                            smjerPacmana = 1;
                            smjerDuha[0] = 1;
                            smjerDuha[1] = 0;
                            smjerDuha[2] = 3;
                            return;
                        } else //Ako vise nema zivota.
                        {
                            if (isGameFinished)
                                return; //Ako se ovaj blok pozove dvaput.
                            else
                                isGameFinished=true;
                            alert("Game over! Your score: " + score + ". Hope you enjoyed. Author: Teo Samarzija.");
                            clearInterval(time1); //Prestani pratiti gdje se nalazi PacMan, a gdje duhovi.
                            clearInterval(time2); //Zaustavi animacije.
                            if (score>(highscore)*1)
                            {
                                document.getElementById("instructions").style.top=(458+2*50+5)+"px";
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
                    if (Math.abs(xKoordinataDuha[i] - xKoordinataPacmana) < 2 && Math.abs(yKoordinataDuha[i] - yKoordinataPacmana) < 2 && brojacGlavnePetlje - kadaJePacmanPojeoVelikuTocku < 30) //Ako PacMan pojede "plavog" duha.
                    {
                        xKoordinataSiluete[i]=xKoordinataDuha[i];
                        yKoordinataSiluete[i]=yKoordinataDuha[i];
                        score += 10 + 2 * level;
                        jeLiPacmanPojeoDuha[i] = true;
                        xKoordinataDuha[i] = pocetnaXKoordinataDuha[i];
                        yKoordinataDuha[i] = pocetnaYKoordinataDuha[i];
                        continue;
                    }
                }
                for (var i=0; i<3; i++)
                {
                    if (xKoordinataSiluete[i]==xKoordinataCiljaSiluete && (yKoordinataSiluete[i]==yKoordinataCiljaSiluete || yKoordinataSiluete[i]==yKoordinataCiljaSiluete+1)) //Ako je bijeli duh pred vratima...
                        {smjerKretanjaSiluete[i]=2; continue;} //... neka ude u kucicu.
                    if (xKoordinataSiluete[i]==xKoordinataCiljaSiluete && yKoordinataSiluete[i]==yKoordinataCiljaSiluete+2 && smjerKretanjaSiluete[i]==2) //Ako je bijeli duh upravo usao u kucicu...
                        {smjerKretanjaSiluete[i]=3; continue;} //... neka ide lijevo.
                    if (xKoordinataSiluete[i]==xKoordinataCiljaSiluete && yKoordinataSiluete[i]==yKoordinataCiljaSiluete+2) //Ako je bijeli duh na sredini kucice...
                        continue; //Neka zadrzi smjer.
                    if (xKoordinataSiluete[i]==xKoordinataCiljaSiluete-1 && yKoordinataSiluete[i]==yKoordinataCiljaSiluete+2) //Ako je bijeli duh na lijevom zidu kucice
                        {smjerKretanjaSiluete[i]=1; continue} //... neka ide desno.
                    if (xKoordinataSiluete[i]==xKoordinataCiljaSiluete+1 && yKoordinataSiluete[i]==yKoordinataCiljaSiluete+2) //Ako je bijeli duh na desnom zidu kucice...
                        {smjerKretanjaSiluete[i]=3; continue;} //... neka ide lijevo.
                    if ((Math.abs(xKoordinataSiluete[i]-xKoordinataCiljaSiluete)==1 || Math.abs(xKoordinataSiluete[i]-xKoordinataCiljaSiluete)==2) &&
                        pocetnoStanjeLabirinta[yKoordinataSiluete[i]+yKomponentaSmjeraPacmana[smjerKretanjaSiluete[i]]].charAt(xKoordinataSiluete[i]+xKomponentaSmjeraPacmana[smjerKretanjaSiluete[i]])!='W' && smjerKretanjaSiluete[i]-4)
                        continue; //Greedy algoritam za odabiranje smjera ne funkcionira u nekim slucajevima.
                    if (yKoordinataSiluete[i]>yKoordinataCiljaSiluete && pocetnoStanjeLabirinta[yKoordinataSiluete[i]-1].charAt(xKoordinataSiluete[i])!='W') //Ako je bijeli duh ispod cilja...
                            {smjerKretanjaSiluete[i]=0; continue;} //... usmjeri ga prema gore.
                    if (yKoordinataSiluete[i]<yKoordinataCiljaSiluete && pocetnoStanjeLabirinta[yKoordinataSiluete[i]+1].charAt(xKoordinataSiluete[i])!='W') //Ako je bijeli duh iznad cilja...
                            {smjerKretanjaSiluete[i]=2; continue;} //... usmjeri ga prema dolje.
                    if (xKoordinataSiluete[i]>xKoordinataCiljaSiluete && pocetnoStanjeLabirinta[yKoordinataSiluete[i]].charAt(xKoordinataSiluete[i]-1)!='W') //Ako je bijeli duh desno od cilja...
                            {smjerKretanjaSiluete[i]=3; continue;} //... usmjeri ga prema lijevo.
                    if (xKoordinataSiluete[i]<xKoordinataCiljaSiluete && pocetnoStanjeLabirinta[yKoordinataSiluete[i]].charAt(xKoordinataSiluete[i]+1)!='W') //Ako je bijeli duh lijevo od cilja...
                            {smjerKretanjaSiluete[i]=1; continue;} //... usmjeri ga prema desno.
                    if (xKoordinataSiluete[i]==xKoordinataCiljaSiluete && yKoordinataSiluete[i]>yKoordinataCiljaSiluete && pocetnoStanjeLabirinta[yKoordinataSiluete[i]-1].charAt(xKoordinataSiluete[i])=='W') //Ako je bijeli duh ravno ispod cilja, a iznad njega zid...
                            {smjerKretanjaSiluete[i]=3; continue;} //... usmjeri ga prema lijevo.
                }
                if (zaslon.getElementById("krug" + (yKoordinataPacmana * 20 + xKoordinataPacmana)) != null) { //Ako pojede tockicu.
                    zaslon.removeChild(zaslon.getElementById("krug" + (yKoordinataPacmana * 20 + xKoordinataPacmana)));
                    if (pocetnoStanjeLabirinta[yKoordinataPacmana].charAt(xKoordinataPacmana) == "B") //Ako je upravo pojedena velika tocka.
                    {
                        kadaJePacmanPojeoVelikuTocku = brojacGlavnePetlje;
                        score += 4 + level;
                        jeLiPacmanPojeoDuha = [false, false, false];
                    }
                    score += 1 + level;
                    howManyDotsHasPacmanEaten++;
                    var bodovi = document.getElementById("score");
                    bodovi.removeChild(bodovi.lastChild);
                    bodovi.appendChild(document.createTextNode("Score: " + score));
                }
                drawGhosts();
                drawPacMan();
                var touch = document.createElementNS(XML_namespace_of_SVG, "rect"); //Prozirni pravokutnik preko labirinta prima evente kada korsnik dodirne negdje u labirint.
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
                    if (jeLiPacmanPojeoDuha[i] && brojacGlavnePetlje - kadaJePacmanPojeoVelikuTocku < 30)
                    {
                        xKoordinataSiluete[i]+=xKomponentaSmjeraPacmana[smjerKretanjaSiluete[i]];
                        yKoordinataSiluete[i]+=yKomponentaSmjeraPacmana[smjerKretanjaSiluete[i]];
                        continue;
                    }
                    xKoordinataDuha[i] += xKomponentaSmjeraPacmana[smjerDuha[i]];
                    yKoordinataDuha[i] += yKomponentaSmjeraPacmana[smjerDuha[i]];
                    if (xKoordinataDuha[i] > 14) //Ako duh prode kroz prolaz desno u sredini labirinta.
                        xKoordinataDuha[i] = 0;
                    if (xKoordinataDuha[i] < 0) //Ako duh prode kroz prolaz lijevo u sredini labirinta.
                        xKoordinataDuha[i] = 14;
                }
                xKoordinataPacmana += xKomponentaSmjeraPacmana[smjerPacmana];
                yKoordinataPacmana += yKomponentaSmjeraPacmana[smjerPacmana];
                if (xKoordinataPacmana > 14) //Ako PacMan prode kroz desni prolaz.
                    xKoordinataPacmana = 0;
                if (xKoordinataPacmana < 0) //Ako PacMan prode kroz lijevi prolaz.
                    xKoordinataPacmana = 14;
                if (howManyDotsHasPacmanEaten == howManyDotsAreThere)
                    nextLevel();
            }
            function animationLoop()
            {
                if (brojacGlavnePetlje < 2)
                    return; //Ne pokusavaj animirati PacMana i duhove ako jos nisu nacrtani.
                brojacAnimacijskePetlje++;
                for (var i = 0; i < 3; i++) {
                    if (jeLiPacmanPojeoDuha[i] && brojacGlavnePetlje - kadaJePacmanPojeoVelikuTocku < 30) //Ako je PacMan nedavno pojeo duha, animiraj bijelu siluetu...
                     zaslon.getElementById("bijeli" + (i + 1)).setAttribute("transform",
                            "translate(" + (20 / 5) * brojacAnimacijskePetlje * xKomponentaSmjeraPacmana[smjerKretanjaSiluete[i]] + " " + (20 / 5) * brojacAnimacijskePetlje * yKomponentaSmjeraPacmana[smjerKretanjaSiluete[i]] + ")");
                    else //... inace animiraj duha.   
                        zaslon.getElementById("duh" + (i + 1)).setAttribute("transform",
                            "translate(" + (20 / 5) * brojacAnimacijskePetlje * xKomponentaSmjeraPacmana[smjerDuha[i]] + " " + (20 / 5) * brojacAnimacijskePetlje * yKomponentaSmjeraPacmana[smjerDuha[i]] + ")");
                }
                if (hasPacmanChangedDirection == true) //Nemoj animirati PacMana ukoliko on upravo mijenja smjer.
                    return;
                zaslon.getElementById("PacMan").setAttribute("transform",
                        "translate(" + (20 / 5) * brojacAnimacijskePetlje * xKomponentaSmjeraPacmana[smjerPacmana] + " " + (20 / 5) * brojacAnimacijskePetlje * yKomponentaSmjeraPacmana[smjerPacmana] + ")");
                var usta = zaslon.getElementById("usta");
                usta.setAttribute("transform",
                        "translate(" + ((20 / 5) * brojacAnimacijskePetlje * xKomponentaSmjeraPacmana[smjerPacmana] + ((xKoordinataPacmana - xKomponentaSmjeraPacmana[smjerPacmana]) * 20 + 10))
                        + " " + ((20 / 5) * brojacAnimacijskePetlje * yKomponentaSmjeraPacmana[smjerPacmana] + (yKoordinataPacmana - yKomponentaSmjeraPacmana[smjerPacmana]) * 20 + 10) + ")");
                if (!((xKoordinataPacmana + yKoordinataPacmana) % 2) /*Na poljima na parnim dijagonalama ce usta biti zatvorena, a na neparnima otvorena.*/ && (smjerPacmana == 1 || smjerPacmana == 3))
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(1 " + (brojacAnimacijskePetlje * 0.2) + ")");
                else if (smjerPacmana == 1 || smjerPacmana == 3)
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(1 " + (1 - brojacAnimacijskePetlje * 0.2) + ")");
                else if (!((xKoordinataPacmana + yKoordinataPacmana) % 2) && (smjerPacmana == 2 || !smjerPacmana))
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(" + (brojacAnimacijskePetlje * 0.2) + " 1)");
                else if (smjerPacmana == 2 || !smjerPacmana)
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(" + (1 - brojacAnimacijskePetlje * 0.2) + " 1)");
                else if (smjerPacmana == 4) //PacMan, ako se ne mice, uvijek drzi usta zatvorenima.
                    usta.setAttribute("transform",
                            usta.getAttribute("transform") + " scale(1 0)");
                usta.setAttribute("transform",
                        usta.getAttribute("transform") + " rotate(" + (90 * smjerPacmana - 90) + ")");
            }
            //Crtanje labirinta na pocetku igre.
            for (var i = 0; i < 19; i++)
                for (var j = 0; j < 15; j++)
                {
                    if (pocetnoStanjeLabirinta[i].charAt(j) == 'W')
                    {
                        if (pocetnoStanjeLabirinta[i - 1].charAt(j) == 'W')
                            drawLine(j * 20 + 10, j * 20 + 10, i * 20, i * 20 + 10);
                        if (pocetnoStanjeLabirinta[i + 1].charAt(j) == 'W')
                            drawLine(j * 20 + 10, j * 20 + 10, i * 20 + 10, i * 20 + 20);
                        if (pocetnoStanjeLabirinta[i].charAt(j - 1) == 'W')
                            drawLine(j * 20, j * 20 + 10, i * 20 + 10, i * 20 + 10);
                        if (pocetnoStanjeLabirinta[i].charAt(j + 1) == 'W')
                            drawLine(j * 20 + 10, j * 20 + 20, i * 20 + 10, i * 20 + 10);
                    }
                    if (pocetnoStanjeLabirinta[i].charAt(j) == 'P')
                    {
                        drawSmallCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                        howManyDotsAreThere++;
                    }
                    if (pocetnoStanjeLabirinta[i].charAt(j) == 'B')
                    {
                        drawBigCircle(j * 20 + 10, i * 20 + 10, "krug" + (i * 20 + j));
                        howManyDotsAreThere++;
                    }
                    if (pocetnoStanjeLabirinta[i].charAt(j) == 'C')
                    {
                        xKoordinataPacmana = pocetnaXKoordinataPacmana = j;
                        yKoordinataPacmana = pocetnaYKoordinataPacmana = i;
                    }
                    if (pocetnoStanjeLabirinta[i].charAt(j) > '0' && pocetnoStanjeLabirinta[i].charAt(j) < '4') //Duhovi.
                    {
                        //charCodeAt - vraca ASCII vrijednost znaka iz stringa (broj), to je vazno zbog arraysova, arr['0'] ne znaci isto sto i arr[0].
                        xKoordinataDuha[pocetnoStanjeLabirinta[i].charCodeAt(j) - "1".charCodeAt(0)] = j;
                        yKoordinataDuha[pocetnoStanjeLabirinta[i].charCodeAt(j) - "1".charCodeAt(0)] = i;
                        pocetnaYKoordinataDuha[pocetnoStanjeLabirinta[i].charCodeAt(j) - "1".charCodeAt(0)] = i;
                        pocetnaXKoordinataDuha[pocetnoStanjeLabirinta[i].charCodeAt(j) - "1".charCodeAt(0)] = j;

                    }
                }
            //Crtanje PacMana u lijevom donjem kutu koji oznacaju preostale zivote.
            for (var i = 0; i < kolikoJePacmanuPreostaloZivota; i++)
            {
                var krug = document.createElementNS(XML_namespace_of_SVG, "circle");
                krug.setAttribute("fill", "yellow");
                krug.setAttribute("cx", 25 + i * 25);
                krug.setAttribute("cy", 380);
                krug.setAttribute("r", 10);
                krug.setAttribute("id", "live" + (i + 1));
                zaslon.appendChild(krug);
                var usta = document.createElementNS(XML_namespace_of_SVG, "polygon");
                usta.setAttribute("points",
                        (25 + i * 25) + ",380 " + (35 + i * 25) + ",370 " + (35 + i * 25) + " 390");
                usta.setAttribute("fill", "black");
                zaslon.appendChild(usta);
            }
            drawGhosts();
            drawPacMan();
            function onStartButton()
            {
				document.body.removeChild(document.getElementById("startButton"));
                showLevel(); //U funkciji "showlevel" se postavlja timer.
            }
            function nestajanje() //Natpis o tome na kojem smo levelu ne iscezava odjednom, nego postupno.
            {
                var natpis=document.getElementById("natpis");
                if (kolikoJePutaDuhPromijenioSmjer<16)
                {
                    kolikoJePutaDuhPromijenioSmjer++;
                    natpis.style.opacity-=1/15;
                    natpis.style.left=(document.body.clientWidth/2-300/2+50+kolikoJePutaDuhPromijenioSmjer)+"px"; //Kako natpis nestaje, polako se pomice udesno.
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
                natpis.style.opacity=1.0;
                natpis.style.left=document.body.clientWidth/2-300/2+50;
                natpis.innerHTML="<b>LEVEL #"+(level+1)+"<\/b>"; //Ovako se mogu pozivati naredbe iz HTML-a u JavaScript programu.
                natpis.id="natpis";
                document.body.appendChild(natpis);
                kolikoJePutaDuhPromijenioSmjer=0;
                setTimeout(nestajanje,500); //Neka natpis o tome na kojem smo levelu pocne iscezavati nakon 500 milisekundi.
                setTimeout(function(){
                            time1 = window.setInterval(mainLoop, 500);
                            time2 = window.setInterval(animationLoop, 100);
                           },2000); //Neka se glavna i animacijska petlja pocnu vrtiti nakon 2000 milisekunda od trenutka kada prijedemo na novi level.
            }
        </script>
    </body>
</html>
