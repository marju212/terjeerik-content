# terjeerik-content

Innehållet till **Anteckningar från Löt** (terjeerik.se) — en Bludit-blogg.
Detta repo innehåller enbart *publicerat* innehåll: inläggstexter, sidor,
databaser och uppladdade bilder. Aldrig `users.php` (konton/hemligheter).

## Hur innehållet hänger ihop

Ett inlägg består av **fyra** samordnade delar:

1. `pages/<slug>/index.txt` — brödtexten (Markdown; HTML som `<figure>` fungerar också)
2. En post i `databases/pages.php` — titel, beskrivning, datum, kategori, uuid m.m.
3. Slug:en tillagd i kategorins `list` i `databases/categories.php`
4. `databases/pages.php` **omsorterad på datum, nyast först** — Bludit läser
   flödets ordning ur filens ordning

Bilder läggs i `uploads/pages/<uuid>/` och refereras rot-relativt:
`/bl-content/uploads/pages/<uuid>/bild.jpg`. Sätt gärna samma bild som
`coverImage` i databasposten (filnamnet, utan sökväg) — då används den som
delningsbild (Open Graph).

## Skapa ett nytt inlägg

Använd skapa-skriptet — det gör alla fyra stegen rätt:

```bash
php bin/nytt-inlagg.php \
  --slug=mitt-nya-inlagg \
  --titel="Min titel" \
  --kategori=mat-halsa \
  --beskrivning="En mening som blir ingress i flödet och delningstext."
```

Kategorier: `mat-halsa`, `landsbygd`, `beredskap`, `personligt`.
Skriv sedan brödtexten i `pages/<slug>/index.txt`, committa och pusha.

## Sanningsordning (viktigt!)

- **Före lansering:** detta repo är sanningen; första deployen seedas härifrån.
- **Efter lansering:** live-sajtens volym är sanningen. Erik skriver i admin på
  live; ett nattligt jobb speglar publicerat innehåll HIT. Inlägg som committas
  direkt hit når INTE live automatiskt — och skrivs över av nästa spegling.
  Efter lansering: skapa inlägg via live-admin eller Bludits API –
  kodrepots `bin/posta-inlagg.php` postar via API mot både lokalt och live.
