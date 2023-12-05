(function($) {

  var app = $.sammy('#app', function() {
    // this.debug = true;

    this.bind('run', function() {
       var context = this;
    });

    this.get('#/', function(context) {
      $.getJSON("api/portrait", function(response) {
        window.scrollTo(0,0);
        $(context.$element()).empty();
        home_page(response).appendTo(context.$element());
        change_title('One80');
        portraitTeaser_cursor();
      });
    });

    this.get('#/portrait/:slug/', function(context) {
      let portrait_slug = this.params.slug;
      $.getJSON("api/portrait/"+portrait_slug, function(response) {
        window.scrollTo(0,0);
        $(context.$element()).empty();
        portrait_route(response).appendTo(context.$element());
        change_title(response.data.title + ' – One80');
        $('header').hide();

        $('video').get(0).play().then().catch((error) => {
          if (error.name === 'NotAllowedError') {
            portraitFull_movie_actions_play($('.portraitFull_movie_actions .portraitFull_movie_actions_play'))
            $('<div class="portraitFull_movie_play"><button type="button" onclick="portraitFull_movie_fullPlay(this)">Play</button></div>').appendTo('.portraitFull_movie')
          }
        });
      });
    });

    this.get('#/about/', function(context) {
      window.scrollTo(0,0);
      $(context.$element()).empty();
      about_route().appendTo(context.$element());
      change_title('About' + ' – One80');
    });

    this.get('#/contact/', function(context) {
      window.scrollTo(0,0);
      $(context.$element()).empty();
      contact_route().appendTo(context.$element());
      change_title('Contact' + ' – One80');
    });

    this.get('#/behind-the-scenes/', function(context) {
      window.scrollTo(0,0);
      $(context.$element()).empty();
      behindthescenes_route().appendTo(context.$element());
      change_title('Behind-the-scenes' + ' – One80');
    });

    this.get('#/legal-notice/', function(context) {
      window.scrollTo(0,0);
      $(context.$element()).empty();
      legalnotice_route().appendTo(context.$element());
      change_title('Leagal Notice' + ' – One80');
    });

    this.before('.*', function() {
      var hash = document.location.hash;
      $("nav").find("a").removeClass("current");
      $("nav").find("a[href='"+hash+"']").addClass("current");
    });

  });

  $(function() {
   app.run('#/');
  });

})(jQuery);


function home_page(response) {
  return $(`
    <main>
      `+ get_home_sections(response.data) +`
    </main>
    `);

    function get_home_sections(data) {
      var return_html = "";
      for (var key in data) {
        return_html += render_home_section(data[key].title, data[key].slug, data[key].protagonist, data[key].thumbnail, data[key].teaser);
      }
      return return_html;
    }
}

function render_home_section(title, slug, protagonist, thumbnail, teaser) {
  return `
    <article class="portraitTeaser" data-path="#/portrait/`+slug+`/" onclick="openPortrait(this)">
      <video src="`+teaser+`" poster="`+thumbnail+`" muted autoplay loop playsinline></video>
      <div class="portraitTeaser_text">
        <h1>`+title+`</h1>
        <h2>`+protagonist+`</h2>
      </div>
      <div class="portraitTeaser_overlay"></div>
      <div class="portraitTeaser_cursor"><span>Play</span></div>
    </article>
    `;
}

function portrait_route(response) {
  return $(`
    <main>
      `+ render_portrait_section(response.data) +`
    </main>
    `);
}

function render_portrait_section(data) {
  return `
    <section class="portraitFull_movie">
      <div class="portraitFull_movie_actions">
        <button type="button" class="portraitFull_movie_actions_play" onclick="portraitFull_movie_actions_play(this)" data-action="pause" title="pause">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pause"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
        </button>
        <button type="button" class="portraitFull_movie_actions_mute" onclick="portraitFull_movie_actions_mute(this)" data-action="mute" title="mute">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-volume-2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
        </button>
        <button type="button" class="portraitFull_movie_actions_close" onclick="portraitFull_movie_actions_close()" title="close">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> 
        </button>
      </div>
      <video src="`+data.portrait+`" poster="`+data.thumbnail+`" loop playsinline></video>
      <div class="portraitFull_movie_arrow"><button type="button" onclick="scrollDown()"></button></div>
    </section>
    <section class="portraitFull_desc">
      <div class="portraitFull_desc_sidebar">
        <h1>`+data.title+`</h1>
        <h2>`+data.protagonist+`</h2>
      </div>
      <div class="portraitFull_desc_content">
        `+get_content(data.content)+`
      </div>
    </section>
  `;

  function get_content(data) {
    var return_html = "";
    for (var key in data) {
      if (data[key].type == "text") {
          return_html += render_content_text(data[key].content);
        }
        if (data[key].type == "image") {
          return_html += render_content_image(data[key].src, data[key].alt);
        }
    }
    return return_html;

    function render_content_text(conent) {
      return `
        <p>`+ conent +`</p>
      `;
    }

    function render_content_image(src, alt) {
      return `
        <img src="`+src+`" alt="`+alt+`" />
      `;
    }
  }
}

function about_route() {
  return $(`
    <main>
      <section class="imageHeader">
        <img src="assets/static/about/image_01.jpeg" alt="">
        <div class="imageHeader_arrow"><button type="button" onclick="scrollDown()"></button></div>
      </section>
      <section class="pageContent">
        <div class="pageContent_sidebar">
          <h1>About</h1>
        </div>
        <div class="pageContent_content">
          <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt</p>
          <div class="pageContent_content_columns">
            <div class="pageContent_content_columns_small">
              <img src="assets/static/about/image_02.jpg" alt="">
            </div>
            <div class="pageContent_content_columns_large">
              <h2>Max musterman</h2>
              <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat</p>
            </div>
          </div>
          <div class="pageContent_content_columns">
            <div class="pageContent_content_columns_large">
              <h2>Max musterman</h2>
              <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat</p>
            </div>
            <div class="pageContent_content_columns_small">
              <img src="assets/static/about/image_03.jpg" alt="">
            </div>
          </div>
        </div>
      </section>
    </main>
  `);
}

function contact_route() {
  return $(`
    <main>
      <section class="imageHeader">
        <img src="assets/static/contact/image_01.jpg" alt="">
        <div class="imageHeader_arrow"><button type="button" onclick="scrollDown()"></button></div>
      </section>
      <section class="pageContent">
        <div class="pageContent_sidebar">
          <h1>contact</h1>
        </div>
        <div class="pageContent_content">
          <form action="" method="post">
            <h1 class="form_title">Say hello!</h1>
            <div class="form_field">
              <input type="text" name="name" id="" placeholder="First- & Lastname">
              <p class="form_field_error"></p>
            </div>
            <div class="form_field">
              <input type="text" name="email" id="" placeholder="E-Mail">
              <p class="form_field_error"></p>
            </div>
            <div class="form_field">
              <textarea name="message" id="" rows="5" placeholder="Message"></textarea>
              <p class="form_field_error"></p>
            </div>
            <button type="submit" onclick="contactFormSubmit(this, event)">Send</button>
          </form>
        </div>
      </section>
    </main>
  `);
}


function behindthescenes_route() {
  return $(`
    <main>
      <section class="imageHeader">
        <img src="assets/static/BTS/image_01.jpg" alt="">
        <div class="imageHeader_arrow"><button type="button" onclick="scrollDown()"></button></div>
      </section>
      <section class="pageContent">
        <div class="pageContent_sidebar">
          <h1>Behind the scenes</h1>
        </div>
        <div class="pageContent_content">
          <img src="assets/static/BTS/image_02.jpg" alt="" />
          <img src="assets/static/BTS/image_03.jpg" alt="" />
          <img src="assets/static/BTS/image_04.jpg" alt="" />
        </div>
      </section>
    </main>
  `);
}

function legalnotice_route() {
  return $(`
    <main>
      <section class="imageHeader">
        <img src="assets/static/legal/image_01.jpg" alt="">
        <div class="imageHeader_arrow"><button type="button" onclick="scrollDown()"></button></div>
      </section>
      <section class="pageContent">
        <div class="pageContent_sidebar">
          <h1>Impressum</h1>
        </div>
        <div class="pageContent_content">
          <h2>Kontakt</h2>
          <p>Marc-Alexis Guerraz<br>
          hello@marcguerraz.com<br>
          <i>Adresse auf Anfrage</i><br>
          Schweiz</p>
          <h2>Haftungsausschluss</h2>
          <p>Der Autor übernimmt keinerlei Gewähr hinsichtlich der inhaltlichen Richtigkeit, Genauigkeit, Aktualität, Zuverlässigkeit und Vollständigkeit der Informationen. Haftungsanspräche gegen den Autor wegen Schäden materieller oder immaterieller Art, welche aus dem Zugriff oder der Nutzung bzw. Nichtnutzung der veröffentlichten Informationen, durch Missbrauch der Verbindung oder durch technische Störungen entstanden sind, werden ausgeschlossen. Alle Angebote sind unverbindlich. Der Autor behält es sich ausdrücklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ankündigung zu verändern, zu ergänzen, zu löschen oder die Veröffentlichung zeitweise oder endgültig einzustellen.</p>
          <h2>Haftung für Links</h2>
          <p>Verweise und Links auf Webseiten Dritter liegen ausserhalb unseres Verantwortungsbereichs Es wird jegliche Verantwortung für solche Webseiten abgelehnt. Der Zugriff und die Nutzung solcher Webseiten erfolgen auf eigene Gefahr des Nutzers oder der Nutzerin.</p>
          <h2>Urheberrechte</h2>
          <p>Die Urheber- und alle anderen Rechte an Inhalten, Bildern, Fotos oder anderen Dateien auf der Website gehören ausschliesslich der Firma Pâtisserie Angot oder den speziell genannten Rechtsinhabern. Für die Reproduktion jeglicher Elemente ist die schriftliche Zustimmung der Urheberrechtsträger im Voraus einzuholen.<br>
          Alle Medien © 2015-2022 Marc Guerraz<br>
          Portrait (“About me” Seite) © 2020-2022 Camille Guerraz</p>
          <h2>Welche personenbezogenen Daten wir sammeln und warum wir sie sammeln</h2>
          <p>Wenn Sie auf diese Webseite zugreifen werden automatisch Informationen allgemeiner Natur erfasst. Diese Informationen werden im Server-Logfile erfasst und beinhalten die Art des Webbrowsers, das verwendete Betriebssystem, den Domainnamen Ihres Internet-Service-Providers, Ihre IP-Adresse und ähnliches.</p>
          <p>Dies aus folgendem Grund:<br>
          – Sicherstellung eines problemlosen Verbindungsaufbaus der Website<br>
          – Sicherstellung einer reibungslosen Nutzung unserer Website<br>
          – Auswertung der Systemsicherheit und Systemstabilität<br>
          – Weitere administrative Zwecken</p>
          <p>Ihre Daten werden nicht verwendet, um Rückschlüsse auf Ihre Person zu ziehen. Informationen dieser Art werden lediglich statistisch ausgewertet, um unseren Internetauftritt und die dahinterstehende Technik zu optimieren.</p>
          <h2>Speicherdauer</h2>
          <p>Die Daten werden gelöscht, sobald diese für den Zweck der Erhebung nicht mehr erforderlich sind. Dies ist für die Daten, die der Bereitstellung der Webseite dienen, grundsätzlich der Fall, wenn die jeweilige Sitzung beendet ist.</p>
          <h2>Kontaktformulare</h2>
          <p>Die von Ihnen eingegebenen Daten werden zum Zweck der individuellen Kommunikation mit Ihnen gespeichert. Hierfür ist die Angabe einer validen E-Mail-Adresse sowie Ihres Namens erforderlich. Diese dient der Zuordnung der Anfrage und der anschließenden Beantwortung derselben. Die Angabe weiterer Daten ist optional.</p>
          <h2>Cookies</h2>
          <p>Wenn Sie die Homepage besuchen, wird ein Sitzungs-Cookie für die Darstellung der Bilder gespeichert.</p>
          <h2>Verwendung von Scriptbibliotheken</h2>
          <h3 style="padding-left: 40px;">Google Webfonts</h3>
          <p style="padding-left: 40px;">Um unsere Inhalte browserübergreifend korrekt und grafisch ansprechend darzustellen, verwenden wir die Website „Google Web Fonts“ der Google LLC (1600 Amphitheatre Parkway, Mountain View, CA 94043, USA; nachfolgend „Google“) zur Darstellung von Schriften.<br>
          Die Datenschutzrichtlinie des Bibliothekbetreibers Google finden Sie hier: <a href="https://www.google.com/policies/privacy/" target="_blank" rel="noopener noreferrer nofollow">https://www.google.com/policies/privacy/</a></p>
          <h3 style="padding-left: 40px;">Cloudflare cdnjs</h3>
          <p style="padding-left: 40px;">Um unsere Inhalte browserübergreifend korrekt und grafisch ansprechend darzustellen, verwenden wir die Website „cdnjs“ der Cloudflare, Inc. (101 Townsend St, San Francisco, CA 94107, USA; nachfolgend „Cloudflare“) zur Lieferung von Javascript und Cascading Style Sheets.<br>
          Die Datenschutzrichtlinie des Bibliothekbetreibers Cloudflare finden Sie hier: <a href="https://www.cloudflare.com/de-de/privacypolicy/" target="_blank" rel="noopener noreferrer nofollow">https://www.cloudflare.com/de-de/privacypolicy/</a></p>
          <h2>Verwendung von Google reCAPTCHA</h2>
          <p>Um unsere Website vor spam zu schützen benutzten wir Google reCAPTCHA der Google LLC (1600 Amphitheatre Parkway, Mountain View, CA 94043, USA; nachfolgend „Google“)<br>
          Die Datenschutzrichtlinie und Geschäftsbedingungen von Google finden Sie hier: <a href="https://www.google.com/policies/privacy/" target="_blank" rel="noopener noreferrer nofollow">https://www.google.com/policies/privacy/</a> und <a href="https://www.google.com/policies/terms" target="_blank" rel="noopener noreferrer nofollow">https://www.google.com/policies/terms</a></p>
          <h2>Eingebettete Inhalte von anderen Websites</h2>
          <p>Beiträge auf dieser Website können eingebettete Inhalte beinhalten (z. B. Videos, Bilder, Beiträge etc.). Eingebettete Inhalte von anderen Websites verhalten sich exakt so, als ob der Besucher die andere Website besucht hätte.</p>
          <p>Diese Websites können Daten über Sie sammeln, Cookies benutzen, zusätzliche Tracking-Dienste von Dritten einbetten und Ihre Interaktion mit diesem eingebetteten Inhalt aufzeichnen, inklusive Ihrer Interaktion mit dem eingebetteten Inhalt, falls Sie ein Konto haben und auf dieser Website angemeldet sind.</p>
          <h2>Verwendung von Google Analytics</h2>
          <p>Diese Website benutzt Google Analytics, einen Webanalysedienst der Google LLC, 1600 Amphitheatre Parkway, Mountain View, CA 94043 USA (nachfolgend: „Google“). Google Analytics verwendet sog. „Cookies“, also Textdateien, die auf Ihrem Computer gespeichert werden und die eine Analyse der Benutzung der Webseite durch Sie ermöglichen. Die durch das Cookie erzeugten Informationen über Ihre Benutzung dieser Webseite werden in der Regel an einen Server von Google in den USA übertragen und dort gespeichert. Aufgrund der Aktivierung der IP-Anonymisierung auf diesen Webseiten, wird Ihre IP-Adresse von Google jedoch innerhalb von Mitgliedstaaten der Europäischen Union oder in anderen Vertragsstaaten des Abkommens über den Europäischen Wirtschaftsraum zuvor gekürzt. Nur in Ausnahmefällen wird die volle IP-Adresse an einen Server von Google in den USA übertragen und dort gekürzt. Im Auftrag des Betreibers dieser Website wird Google diese Informationen benutzen, um Ihre Nutzung der Webseite auszuwerten, um Reports über die Webseitenaktivitäten zusammenzustellen und um weitere mit der Websitenutzung und der Internetnutzung verbundene Dienstleistungen gegenüber dem Webseitenbetreiber zu erbringen. Die im Rahmen von Google Analytics von Ihrem Browser übermittelte IP-Adresse wird nicht mit anderen Daten von Google zusammengeführt.</p>
          <p>Die Zwecke der Datenverarbeitung liegen in der Auswertung der Nutzung der Website und in der Zusammenstellung von Reports über Aktivitäten auf der Website. Auf Grundlage der Nutzung der Website und des Internets sollen dann weitere verbundene Dienstleistungen erbracht werden.</p>
          <h2>Wie lange wir Ihre Daten speichern</h2>
          <p>Wenn Sie einen Kommentar schreiben, wird dieser inklusive Metadaten zeitlich unbegrenzt gespeichert. Auf diese Art können wir Folgekommentare automatisch erkennen und freigeben, anstelle sie in einer Moderations-Warteschlange festzuhalten.</p>
          <p>Für Benutzer, die sich auf unserer Website registrieren, speichern wir zusätzlich die persönlichen Informationen, die sie in ihren Benutzerprofilen angeben. Alle Benutzer können jederzeit ihre persönlichen Informationen einsehen, verändern oder löschen (der Benutzername kann nicht verändert werden). Administratoren der Website können diese Informationen ebenfalls einsehen und verändern.</p>
          <h2>Welche Rechte Sie an Ihren Daten haben</h2>
          <p>Wenn Sie ein Konto auf dieser Website haben, auf dieser Website bestellt haben oder Kommentare&nbsp;auf dieser Website geschrieben haben, können Sie einen Export Ihrer personenbezogenen Daten bei uns anfordern, inklusive aller Daten, die Sie uns mitgeteilt haben. Darüber hinaus können Sie die Löschung aller personenbezogenen Daten, die wir von Ihnen gespeichert haben, anfordern. Dies umfasst nicht die Daten, die wir aufgrund administrativer, rechtlicher oder sicherheitsrelevanter Notwendigkeiten aufbewahren müssen.</p>
          <h2>Wie wir Ihre Daten schützen</h2>
          <h3 style="padding-left: 40px;">SSL-Verschlüsselung</h3>
          <p style="padding-left: 40px;">Um die Sicherheit Ihrer Daten bei der Übertragung zu schützen, verwenden wir dem aktuellen Stand der Technik entsprechende Verschlüsselungsverfahren (z. B. SSL) über HTTPS.</p>
          <h2>Änderungen</h2>
          <p>Wir können diese Datenschutzerklärung jederzeit ohne Vorankündigung anpassen. Es gilt die jeweils aktuelle, auf unserer Website publizierte Fassung.</p>
          <h2>Fragen an den Datenschutzbeauftragten</strong></h2>
          <p>Wenn Sie Fragen zum Datenschutz haben, schreiben Sie uns bitte eine E-Mail oder wenden Sie sich direkt an die für den Datenschutz zu Beginn der Datenschutzerklärung aufgeführten, verantwortlichen Person in unserer Organisation.</p>
        </div>
      </section>
    </main>
  `);
}