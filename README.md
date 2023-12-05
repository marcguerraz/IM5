# IM5

## Ziel

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
