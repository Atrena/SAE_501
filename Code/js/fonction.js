function verifierCoefficient() {

    var retour = false
    var coefficient = document.getElementById('id_Coefficient').value;
    let msg = document.getElementById("erreur");
    msg.innerHTML = "";

    switch (true) {
        case coefficient < 1:
            msg.innerHTML = "Le coefficient doit être suppérieur à 1";
            break
        case coefficient > 5:
            msg.innerHTML = "Le coefficient doit être inférieur à 5";
            break
    }

    if (coefficient >= 1 && coefficient <= 5) {
        retour = true;
    }
    return retour;
}

function verifPass() {
    var retour = false;
    var reg = /(?=.*[a-z])(?=.*[A-Z])(?=.*[#\$\&\%\@])/;
    var mdp = document.getElementById('pass').value;
    let msg = document.getElementById("erreur");
    console.log(1);

    switch(true){
        case mdp=="" :
            msg.innerHTML = "Le mot de passe doit être rempli";
            break;
        case reg.test(mdp) == false :
            msg.innerHTML = "Le mot de passe ne correspond pas aux conditions (1 majuscule, 1 minuscule, 1 cacractère spécial)";
            break;
    }
    if(mdp!="" && reg.test(mdp)){
        msg.innerHTML = "";
        retour = true;
    }
    return retour;
}

function update(form) {
    var data = new FormData(form);
    var req_AJAX = null;
    var captcha = data.get("captcha");
    var nomNote = data.get("nomNote");
    var nomMat = data.get("nomMat");
    var coef = data.get("coef");
    var old_noNote = data.get("old_noNote");
    var old_noMat = data.get("old_noMat");
    var old_coef = data.get("old_coef");
    if (window.XMLHttpRequest) {
        req_AJAX = new XMLHttpRequest();
    } else {
        if (typeof ActiveXObject != "undefined") {
            req_AJAX = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }

    if (req_AJAX) {
        req_AJAX.open("POST", "page_ajax/update.php", false);
        req_AJAX.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req_AJAX.send("nomNote=" + nomNote + "&nomMat=" + encodeURIComponent(nomMat) + "&coef=" + coef + "&captcha=" + captcha + "&old_coef=" + old_coef + "&old_noNote=" + old_noNote + "&old_noMat=" + old_noMat);
        var erreur = document.getElementById("erreur");
        data = req_AJAX.responseText;
        erreur.innerHTML = data;
        console.log(erreur);
        var status = req_AJAX.status;
        if (status != 200) {
            throw new Error();
        }
    } else {
        alert("EnvoiRequete: pas de XMLHTTP !");
    }
}

function EnvoiRequete(event, form) {
    event.preventDefault();
    if (verifierCoefficient() == false){
        return;
    }
    update(form);
    var req_AJAX = null;
    if (window.XMLHttpRequest) {
        req_AJAX = new XMLHttpRequest();
    } else {
        if (typeof ActiveXObject != "undefined") {
            req_AJAX = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }

    if (req_AJAX) {
        req_AJAX.onreadystatechange = function () {
            TraiteReponse(req_AJAX);
        }
        req_AJAX.open("POST", "page_ajax/tableau_serveur.php", true); 
        req_AJAX.setRequestHeader("Content-Type", "text/plain");  
        req_AJAX.send("action=marche"); 	
    } else {
        alert("EnvoiRequete: pas de XMLHTTP !");
    }
    var form_modif = document.getElementById("form_modif");
    form_modif.remove();
}

function TraiteReponse(requete) {
    var etat = requete.readyState;
    if (etat == 4) {
        var vide = document.getElementById("vide");
        var status = requete.status; 
        if (status == 200) {
            var data = "";
            data = requete.responseText;
            vide.innerHTML = data;
        } else {
            vide.innerHTML = "erreur serveur, code " + status;
        }
    }
}

function EnvoiRequeteFiltreNote(event, form) {
    event.preventDefault();
    var data = new FormData(form);
    var value = data.get("noNote");
    var req_AJAX = null; // Objet qui sera crée
    if (window.XMLHttpRequest) { // Mozilla, Safari
        req_AJAX = new XMLHttpRequest();
    } else
        if (typeof ActiveXObject != "undefined") { // IE
            req_AJAX = new ActiveXObject("Microsoft.XMLHTTP");
        }
    if (req_AJAX) {
        req_AJAX.onreadystatechange = function () {
            TraiteReponse(req_AJAX);
        };
        req_AJAX.open("POST", "page_ajax/tableau_filtre.php", true);
        req_AJAX.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");  
        req_AJAX.send("noNote="+value); 
    } else {
        alert("EnvoiRequete: pas de XMLHTTP !");
    }
    return false;
}

function EnvoiRequeteFiltreMat(event, form) {
    event.preventDefault();
    var data = new FormData(form);
    var value = data.get("noMat");
    var req_AJAX = null; // Objet qui sera crée
    if (window.XMLHttpRequest) { // Mozilla, Safari
        req_AJAX = new XMLHttpRequest();
    } else
        if (typeof ActiveXObject != "undefined") { // IE
            req_AJAX = new ActiveXObject("Microsoft.XMLHTTP");
        }
    if (req_AJAX) {
        req_AJAX.onreadystatechange = function () {
            TraiteReponse(req_AJAX);
        };
        req_AJAX.open("POST", "page_ajax/tableau_filtre.php", true);
        req_AJAX.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");  
        req_AJAX.send("noMat="+value); 
    } else {
        alert("EnvoiRequete: pas de XMLHTTP !");
    }
    return false;
}
