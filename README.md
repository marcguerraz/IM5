# IM5

## Ziel
Diese Webseite dient der optimalen und strukturierten Präsentation des Endergebnisses unseres Lehrprojekt (durchgeführt von Simon Girschweiler und mir). Sie ist in vier Teile gegliedert. Auf der Hauptseite sind die fertigen Kurzvideoportraits zu sehen. Diese werden durch ein kurzes Intro angeteasert und erscheinen, sobald der Benutzer auf das jeweilige Portrait klickt. Beim Weiterscrollen werden Synopsis und die Rollenverteilung hinter dem Portrait angezeigt, zusammen mit einigen besonders schönen Frames aus dem Film. Weiterhin gibt es eine Teamseite, auf der die Mitwirkenden vorgestellt werden, sowie eine Kontaktseite, um Fragen, Anmerkungen, etc. einreichen zu können. Schliesslich gibt es noch eine Behind-the-Scenes-Seite mit Bildern von der Produktion und Umsetzung der Filmserie. Das Ganze bietet einen umfassenden Überblick über das gesamte Projekt und ermöglicht es auch, sich in ein bestimmtes Videoportrait zu vertiefen.

## Technologies
Die Website wurde mithilfe von HTML und CSS erstellt. Das Kontaktformular wurde mittels PHP und JavaScript umgesetzt, wobei auch Funktionen aus der jQuery-Bibliothek verwendet wurden. Für die Implementierung der no-reload-Navigation wurde ein jQuery-Plugin genutzt, das Client-seitiges Routing ermöglicht. Da es keinen Reload gibt, wurde die Datenabfrage mithilfe von AJAX und einer PHP-API durchgeführt.

## Challenges
Die Herausforderung bestand darin, Videos automatisch mit Ton abzuspielen, auch als Autoplay bekannt. Standardmässig blockiert der Browser dies, es sei denn, es wurde bereits eine Interaktion auf der Website erkannt. Daher war es nur möglich, Autoplay zu verwenden, nachdem auf ein Porträtvideo geklickt wurde und sich das Video in einem neuen Fenster geöffnet hatte. Der Code versucht, das Video immer abzuspielen, erhält jedoch eine Fehlermeldung, wenn noch keine Interaktion auf der Website erfolgt ist. Danach wird ein Wiedergabe-Button in der Mitte der Seite eingeblendet, damit der Benutzer weiss, dass er auf die Website klicken muss. Autoplay funktioniert nur nicht, wenn auch Ton wiedergegeben werden muss. Aus diesem Grund war es dennoch möglich, die Videos auf der Startseite direkt abzuspielen, da sie keinen Ton benötigen.

## Quellen
- Kursunterlagen
- Stackoverflow
- MDN Webdox
- php.net
- ChatGPT
