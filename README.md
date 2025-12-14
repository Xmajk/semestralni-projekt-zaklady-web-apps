# Registrační systém pro Kacubo kendo klub

> [!CAUTION]
> Pro testování byl vytvořen admin
> username: test
> password: heslo123

> [!NOTE]
> navázaná podmínka na inputy, jeden z parametrů je u vytváření nového eventu začátek eventu musí být později než deadline přihlášení, viz HODNOCEN9 SEMESTRÁLKY.ods v repozitáři

## Zadání

> Dodzo Kazubo, mě požádalo jestli bych nevytvořil rezervační systém pro jejich akce, tak že stránka bude mít login, hlavní stránku s akcemi na které se bude moct uživatel přihlásit, nějaký admin pro CRUD tabulek. Naštěstí jejich hosting má stejnou prahistorickou technologii ve které má být i semestrálka, takže s technologiemi by neměl být problém. Chci aby rezervační systém plně seděl do jejich stránek, takže chci použít i jejich sadu kaskádových stylů, a potřebný zbytek vytvořím já. Stránky Kacuba ([https://www.kacubo.cz/index.htm](https://www.kacubo.cz/index.htm)). Budu používat i databázi, kde budou tabulky User, Event, registerEvent

---

# Produktová dokumentace
## Ukázky uživatelského rozhraní (UI)

### Admin
![a](Snímek%20obrazovky%20z%202025-12-14%2020-35-18.png)
### Admin - uživatelé
![a](Snímek%20obrazovky%20z%202025-12-14%2020-35-25.png)
### Admin - editace uživatele
![a](Snímek%20obrazovky%20z%202025-12-14%2020-35-33.png)
### Event
![a](Snímek%20obrazovky%20z%202025-12-14%2022-50-12.png)

Zde je upravená verze dokumentace `README.md` bez emotikonů a s opravenou specifikací ukládání hesel.

-----

# Rezervační systém – Kacubó Kenrikai

**Semestrální projekt – Základy webových aplikací**

Tento projekt představuje webovou aplikaci pro správu událostí, registraci uživatelů a administraci členů pro klub bojových umění Kacubó Kenrikai. Aplikace umožňuje členům prohlížet nadcházející akce a administrátorům plně spravovat obsah webu.

## Klíčové vlastnosti

* **Autentizace a Autorizace:** Přihlašování uživatelů, bezpečné ukládání hesel (funkce `password_hash`), správa session a cookies. Rozlišení rolí `User` a `Admin`.
* **Správa událostí (Event Management):** Vytváření, editace a mazání událostí včetně nahrávání obrázků a nastavování kapacit a termínů.
* **Správa uživatelů:** Admin může vytvářet, upravovat a mazat uživatele, měnit jim práva a hesla.
* **Pokročilá validace:** Dvojitá kontrola dat (klientská strana v JS + serverová strana v PHP).
* **Zpracování obrázků:** Automatický resize a komprese nahraných obrázků (GD library) pro náhledy a detail.

-----

## Logika Událostí a Deadlinů

Systém pracuje s několika časovými údaji, které ovlivňují chování aplikace a dostupnost registrací. Každá událost je definována:

1.  **Datum a čas konání (`start_datetime`)**: Kdy akce fyzicky začíná.
2.  **Datum a čas konce registrací (`registration_deadline`)**: Do kdy je možné se na akci přihlásit.

### Životní cyklus události (Schéma)

Aplikace rozlišuje stavy události na základě aktuálního času serveru a kapacity:

```text
[ Vytvoření události Adminem ]
        |
        v
+-----------------------+
|  STAV: OTEVŘENO (Available) |
|  - Aktuální čas < Deadline  |
|  - Počet přihlášených < Kapacita |
|  -> Zobrazuje se tlačítko "Registrovat" |
+-----------------------+
        |
        | (Čas > Deadline) NEBO (Kapacita naplněna)
        v
+-----------------------+
|  STAV: UZAMČENO (Locked)    |
|  - Registrace již nejsou možné  |
|  - Zobrazuje se "Nelze se přihlásit" |
|  - V UI označeno jako "Uzamčeno" |
+-----------------------+
        |
        | (Čas > Datum konání)
        v
+-----------------------+
|  STAV: PROBĚHLO / ARCHIV    |
|  - Událost je v minulosti       |
+-----------------------+
```

V administraci (`admin/events.php`) je při vytváření akce prováděna validace, aby `registration_deadline` nebyl nastaven až po `start_datetime`.

-----

## Navigace a Průchod aplikací

Níže je popsáno, jak se v aplikaci pohybovat z pohledu běžného uživatele a administrátora.

### 1\. Veřejná část & Přihlášení

Neregistrovaný návštěvník vidí pouze přihlašovací formulář nebo veřejné informace (pokud jsou dostupné).

* **URL:** `/login.php`
* **Akce:** Uživatel zadá `username` a `password`.
* **Validace:** Pokud uživatel neexistuje nebo nesedí hash hesla, zobrazí se chybová hláška.

### 2\. Dashboard (Hlavní stránka)

Po úspěšném přihlášení je uživatel přesměrován na seznam událostí.

* **URL:** `/rezervacni-system/index.php`
* **Vzhled:** Grid karet s událostmi (CSS Grid).
* **Interakce:** Kliknutím na tlačítko u karty se zobrazí detail nebo se provede akce.

### 3\. Administrace (Pouze pro roli Admin)

Pokud má uživatel v databázi příznak `is_admin = 1`, v navigační liště se objeví položka **Admin**.

* **Rozcestník:** `/rezervacni-system/admin/index.php`
* **Sekce:**
    * **Uživatelé (`users.php`):**
        * Tabulkový výpis všech uživatelů.
        * Filtrování podle uživatelského jména (real-time JS).
        * Tlačítko "Přidat uživatele" (rozbalovací formulář).
        * Editace práv (přepínač Admin ANO/NE) a reset hesla.
    * **Události (`events.php`):**
        * Formulář pro přidání nové akce (včetně uploadu obrázku).
        * Tabulka existujících akcí s možností editace a smazání.
        * Export dat do CSV (`download_event.php`).

-----

## Technická specifikace

### Architektura

Projekt je postaven na čistém PHP bez frameworků, s důrazem na oddělení logiky a zobrazení.

* **Backend:** PHP 8.0+
* **Databáze:** MySQL (připojení přes `mysqli` v `components/Database.php`).
* **Frontend:** HTML5, CSS3 (vlastní styly + Bootstrap 3.3 pro navbar), Vanilla JavaScript.

### Struktura souborů

* `components/` – Opakovaně použitelné části kódu (hlavička, db konektor, auth check).
    * `objects/` – Třídy reprezentující databázové entity (`User.php`, `Event.php`). Obsahují metody `insert()`, `update()`, `getById()`.
* `admin/` – Logika pro správu (chráněno funkcí `check_auth_admin()`).
* `public/` – Statické soubory (CSS, JS, obrázky).
    * `scripts/validation/` – JS skripty pro kontrolu formulářů před odesláním.

### Zabezpečení

1.  **Prepared Statements:** Veškeré SQL dotazy používají parametrizované dotazy (`$stmt->bind_param`), což zabraňuje SQL Injection.
2.  **XSS Ochrana:** Výpis dat v HTML je ošetřen pomocí `htmlspecialchars()`.
3.  **Hesla:** Hesla jsou hašována pomocí moderní funkce `password_hash($password, PASSWORD_DEFAULT)`, nikoliv zastaralým MD5 nebo prostým SHA-256.
4.  **Autorizace:** Každý chráněný soubor začíná kontrolou session/cookies (`check_auth_user()` nebo `check_auth_admin()`).

### Instalace a zprovoznění

1.  Nahrajte soubory na webový server s podporou PHP.
2.  Importujte databázové schéma (tabulky `users` a `events`).
3.  Upravte přihlašovací údaje k databázi v souboru:
    * `rezervacni-system/components/Database.php`
4.  Ujistěte se, že složka `public/imgs/events/` (a podložky `large`, `thumb`) má práva pro zápis (chmod 777 nebo vlastník www-data), aby fungoval upload obrázků.

# Programátorská dokumentace

Tato dokumentace slouží pro seznámení zadavatele a správce s funkcionalitou, instalací a zajímavými technickými aspekty vytvořeného řešení.

## Popis řešení

Systém byl navržen jako "lehká" webová aplikace běžící na nativním PHP. Toto rozhodnutí vychází přímo z nutnosti kompatibility se stávajícím hostingem klubu, který nepodporuje moderní frameworky a nástroje pro správu závislostí (Composer).

Hlavním vizuálním cílem bylo, aby uživatel nepoznal přechod mezi stávajícím webem a novým rezervačním systémem. Toho je docíleno přímým napojením na styly (CSS) webu kacubo.cz.

### Hlavní funkcionalita pro uživatele

1.  **Registrace a přihlášení:** Uživatelé si mohou vytvořit účet. Systém dbá na bezpečnost a hesla ukládá v šifrované podobě.
2.  **Přehled akcí:** Po přihlášení vidí uživatel seznam nadcházejících událostí (semináře, zkoušky, soutěže).
3.  **Rezervace:** Jednoduchým kliknutím se uživatel přihlásí na vybranou akci. Systém hlídá kapacitu a termíny přihlášení.

### Administrátorské rozhraní

Administrátor má přístup do zabezpečené sekce, kde může:

* Vytvářet nové události (včetně nahrávání plakátů/obrázků).
* Editovat a mazat existující události (CRUD).
* Spravovat seznam uživatelů.

## Zajímavá témata a technické detaily

### Bezpečnost a ukládání hesel

Původní koncepty často využívají prosté hashování (např. SHA-256), které je však v dnešní době nedostatečné. Tento systém využívá moderní PHP funkci `password_hash()` s algoritmem `PASSWORD_DEFAULT` (zpravidla Bcrypt).

* **Výhoda:** Ke každému heslu je automaticky generována unikátní "sůl" (salt), což chrání databázi před Rainbow Table útoky. Hesla nejsou nikdy ukládána v čitelné podobě.

<!-- end list -->

```php
// Ukázka implementace při registraci
$user->password = password_hash($form_data["password"], PASSWORD_DEFAULT);
```

### Integrace designu (CSS Injection)

Místo kopírování stylů systém dynamicky linkuje CSS soubory přímo z produkčního webu klienta. Pokud klient aktualizuje vzhled svého hlavního webu (změní barvy menu, fonty), změna se automaticky a okamžitě projeví i v rezervačním systému bez nutnosti zásahu programátora.

### Kompatibilita s Legacy Hostingem

Aplikace je strukturována MVC architekturou (Model-View-Controller), ale bez použití externích knihoven. Využívá pouze standardní knihovny dostupné v běžné instalaci PHP 7.x/8.x, což zaručuje funkčnost i na starších serverech.

## Instalační příručka

### 1\. Příprava databáze

Nahrajte přiložený SQL skript (viz níže) do vaší databáze (např. přes phpMyAdmin). Údaje pro připojení nastavte v souboru `components/Database.php`.

### 2\. Konfigurace cest

V souboru `components/utils/links.php` nastavte proměnnou `$PREFIX` podle toho, v jaké složce na serveru aplikace běží.

* Příklad pro kořenový adresář: `$PREFIX = "";`
* Příklad pro podsložku: `$PREFIX = "/rezervace";`

### 3\. Nastavení oprávnění

Pro správné fungování nahrávání obrázků nastavte práva zápisu (CHMOD 777) pro složku `public/imgs/events/`.

## Databázové schéma

Tabulky jsou navrženy pro maximální efektivitu. Všimněte si změny v délce sloupce pro heslo, aby vyhovovala hashům z funkce `password_hash`.

```sql
-- Tabulka uživatelů
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL, -- Hash generovaný pomocí PASSWORD_DEFAULT
    `firstname` varchar(50) NOT NULL,
    `lastname` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `bdate` date NOT NULL,
    `is_admin` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`)
);

-- Tabulka událostí
CREATE TABLE `events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text,
    `location` varchar(100) NOT NULL,
    `price` int(11) DEFAULT 0,
    `capacity` int(11) NOT NULL,
    `start_datetime` datetime NOT NULL,
    `registration_deadline` datetime NOT NULL,
    `image_filename` varchar(255) DEFAULT 'default.jpg',
    PRIMARY KEY (`id`)
);

-- Propojení (registrace na akci)
CREATE TABLE `registerEvent` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `event_id` int(11) NOT NULL,
    `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
);
```

## Adresářová struktura projektu

```text
/rezervacni-system
├── admin/                  # Administrátorské skripty
├── components/             # Backendová logika
│   ├── objects/            # Modely (User, Event)
│   ├── utils/              # Pomocné funkce
│   └── Database.php        # Config databáze
├── public/                 # Frontendové assety (CSS, JS, Obrázky)
├── index.php               # Hlavní stránka
└── login.php               # Login
```

