# Abo-Mails

Jede `.html`-Datei hier ist ein Template, das in einer Sende-Regel (Einstellungen → Abos) gewählt werden kann. Das Plugin setzt den Inhalt in `layout.html` ein. `layout.html` selbst und diese README tauchen nicht in der Auswahl auf.

Platzhalter schreibt man so: `{{ name }}`. Unbekannte Platzhalter werden leer ausgegeben. Alle Platzhalter stehen in jedem Template zur Verfügung.

| Template | Trigger | Tagesbezogene Platzhalter beziehen sich auf |
|---|---|---|
| `each-day.html` | Jeden Tag im Plan | heute |
| `welcome.html` | Bei Bestätigung (sofort) | den ersten Tag des Plans |
| `rate-recipe.html`, `shopping-list.html` | frei wählbar | den Tag der Regel |

## Platzhalter

**Rahmen**

| Platzhalter | Inhalt |
|---|---|
| `greeting` | Anrede, z. B. „Hallo Timo," |
| `plan`, `plan_url` | Titel des Plans, Link zum Plan |
| `plan_number`, `plan_number_html` | Nummer des Plans („#4812“), als Text bzw. als eingerahmtes Etikett wie auf der Plan-Seite – die drei Templates setzen das Etikett in eigener Zeile rechtsbündig über die Überschrift |
| `weekday` | Wochentag, um den es geht |
| `bring_url` | Öffnet die Einkaufsliste des Plans in Bring! |
| `site_name`, `site_url`, `year` | Seite und Jahr |
| `logo_html`, `logo_url` | Logo der Seite (Child Theme), sonst der Seitenname |
| `unsubscribe_url`, `unsubscribe_html` | Abmelde-Link; `unsubscribe_html` steht schon im Layout |
| `footer_html` | Fußzeile aus Einstellungen → E-Mail; steht schon im Layout, in den Templates nicht verwenden |

**Stile** (Child Theme, für `style="…"`)

| Platzhalter | Wirkung |
|---|---|
| `button_style` | Grüner Button – die Hauptaktion einer Mail |
| `button_outline_style` | Umrandeter Button – eine zweite Aktion daneben |
| `muted_style` | Graue Schrift für Grußzeilen |
| `plan_number_line_style` | Zeile mit `plan_number_html` über der Überschrift, rechtsbündig |
| `plan_number_hero_line_style`, `plan_number_hero_style` | Die riesige Plannummer der Willkommensmail: Zeile (zentriert) und Etikett um `plan_number` |

```html
<p><a href="{{ plan_url }}" style="{{ button_style }}">Zum Plan</a></p>
```

**Mahlzeiten des Tages**

| Platzhalter | Inhalt |
|---|---|
| `meal_list_html` | Mahlzeiten, nach Frühstück / Mittagessen / Abendessen / Snack gruppiert, mit Bild und „Jetzt kochen"-Link |
| `meal_images_html` | Nur die Bilder als Reihe, verlinkt |
| `day_summary_html` | Nährwerte des Tages als hervorgehobene Zeile |
| `day_kcal`, `day_cost`, `currency` | Einzelwerte |

**Nährwerte** – pro Person, eine Portion je Mahlzeit

| Platzhalter | Inhalt |
|---|---|
| `day_nutrition_html` | Unauffällige Zeile: kcal, Eiweiß, Kohlenhydrate, Fett (+ Ballaststoffe, Salz) |
| `nutrition_table_html` | Alle Werte als Tabelle mit Anteil an der Referenzmenge. Die Fußnote gehört dazu und enthält auch den Hinweis, dass die Werte geschätzt sind – im Template nicht wiederholen. |
| `meal_nutrition_html` | Energie und Makros je Gericht |
| `day_protein`, `day_carbs`, `day_fat`, `day_sugar`, `day_fiber`, `day_salt` | Gramm |
| `day_kcal_percent` | Tagesenergie in % der Referenzmenge |

**Zutaten**

| Platzhalter | Inhalt |
|---|---|
| `day_ingredients_html` | Zutaten des Tages, je Kategorie eine Tabelle wie die Nährwerte |
| `shopping_list_html` | Einkaufsliste des ganzen Plans, aufgebaut wie `day_ingredients_html` |

**Bewerten und Fotos** – nur in echten Sendungen gefüllt, in der Vorschau leer

| Platzhalter | Inhalt |
|---|---|
| `rating_links_html` | „Rezept bewerten"-Links des Tages (braucht eine „Recipe Rating Page") |

## Konventionen

- Kopfkommentar: eine Zeile mit dem Trigger, mehr nur, wenn das Template eine Besonderheit hat. Kommentare werden mitverschickt.
- Aufbau: `h1` → `greeting` → ein Satz Einleitung → Inhalt mit `h2` → Button „Zum Plan" → optional eine Grußzeile in `muted_style`.
- Stile nur über die Stil-Platzhalter, keine eigenen Inline-Deklarationen.
- Schriftgrößen: 26 (h1) / 20 (h2) / 16 (Text, h3) / 14 (Nebenangaben) / 12 px (Fußnoten, Tabellenköpfe). `h1` und `h2` ohne Attribute schreiben – `functions.php` setzt ihre Größe beim Versand inline.
- Neue Platzhalter, die nicht vom Plan abhängen, kommen über den Filter `mixmymeals_mail_default_vars` in `functions.php`. Endet der Name auf `_html`, wird der Wert als HTML ausgegeben, auf `_url` als Link, sonst als Text.
