# Registrační systém pro Kacubo kendo klub

## Zadání

> Dodzo Kazubo, mě požádalo jestli bych nevytvořil rezervační systém pro jejich akce, tak že stránka bude mít login, hlavní stránku s akcemi na které se bude moct uživatel přihlásit, nějaký admin pro CRUD tabulek. Naštěstí jejich hosting má stejnou prahistorickou technologii ve které má být i semestrálka, takže s technologiemi by neměl být problém. Chci aby rezervační systém plně seděl do jejich stránek, takže chci použít i jejich sadu kaskádových stylů, a potřebný zbytek vytvořím já. Stránky Kacuba ([https://www.kacubo.cz/index.htm](https://www.kacubo.cz/index.htm)). Budu používat i databázi, kde budou tabulky User, Event, registerEvent

-----

# Produktová dokumentace

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

## Ukázky uživatelského rozhraní (UI)

[a](Snímek%20obrazovky%20z%202025-12-14%2020-35-18.png)
[a](Snímek%20obrazovky%20z%202025-12-14%2020-35-25.png)
[a](Snímek%20obrazovky%20z%202025-12-14%2020-35-33.png)
[a](Snímek%20obrazovky%20z%202025-12-14%2022-50-12.png)

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