# 🩺 Anamnesi Manager

**Anamnesi Manager** è uno strumento web leggero scritto in **PHP + JSON** per gestire la cartella clinica personale di un paziente. Permette di archiviare il profilo anagrafico, i farmaci in uso e lo storico degli esami/visite mediche, organizzati per categoria (es. Cardiologia, Endocrinologia, ecc.).

Sviluppato originariamente per uso personale, è progettato per essere installato su qualsiasi hosting PHP senza bisogno di database: tutto viene salvato su un semplice file `anamnesi.json`.

---

## 📸 Screenshot

> *(Aggiungi screenshot nella cartella `/screenshots` e aggiornali qui sotto)*

| Vista paziente | Pannello Admin |
|---|---|
| ![Vista](screenshots/vista.png) | ![Admin](screenshots/admin.png) |

---

## ✨ Funzionalità

- 🔐 **Accesso con password** separata per vista paziente e pannello admin
- 👤 **Profilo anagrafico** completo (dati personali, medico di base, contatti d'emergenza, allergie…)
- 💊 **Farmaci in uso** con dosaggio, frequenza e note
- 📋 **Storico esami e visite** organizzati per categoria, con:
  - Ordinamento cronologico
  - Stato completato / in attesa
  - Link a documenti su Google Drive
  - Descrizioni con testo formattato (grassetto, colore)
- 🗂️ **Categorie personalizzabili**: aggiungi, rinomina, riordina tramite drag & drop
- 🔗 **Integrazione Google Drive** per categoria
- 🔍 **Ricerca testuale** in tempo reale
- 🖨️ **Stampa ottimizzata** della cartella
- 📱 Responsive (mobile-friendly)

---

## 🚀 Installazione

### Requisiti
- Server PHP ≥ 7.4
- Nessun database richiesto
- La directory deve avere **permessi di scrittura** sul file `anamnesi.json`

### Procedura

1. **Clona il repository** nella directory del tuo server web:
   ```bash
   git clone https://github.com/tuo-username/anamnesi-manager.git
   cd anamnesi-manager
   ```

2. **Copia il file di esempio** e personalizzalo:
   ```bash
   cp anamnesi.json anamnesi.json
   ```
   Modifica `anamnesi.json` con i dati reali del paziente.

3. **Imposta le password** aprendo `index.php` e `admin.php`:

   In `index.php` (riga 2):
   ```php
   $pass_view = "cambiami";   // ← password per la vista paziente
   ```

   In `admin.php` (riga 3):
   ```php
   $pass = "Cambiami_Admin";  // ← password per il pannello admin
   ```

4. **Verifica i permessi** sul file JSON:
   ```bash
   chmod 664 anamnesi.json
   ```

5. Apri il browser su `https://tuo-sito/index.php` e accedi con la password impostata.

---

## 📁 Struttura del progetto

```
anamnesi-manager/
├── index.php          # Vista paziente (frontend)
├── admin.php          # Pannello amministratore
├── anamnesi.json      # Database (generato/gestito dall'app)
├── screenshots/       # Screenshot per il README
├── LICENSE
└── README.md
```

---

## 🔒 Note sulla sicurezza

- Le password sono in chiaro nel codice PHP: **usa HTTPS** e scegli password robuste.
- Non esporre `anamnesi.json` direttamente via web: aggiungi una regola `.htaccess` o equivalente Nginx per bloccare l'accesso diretto al file JSON.
- Esempio `.htaccess`:
  ```apache
  <Files "anamnesi.json">
      Order Allow,Deny
      Deny from all
  </Files>
  ```
- Questo strumento è pensato per **uso personale/familiare** su hosting privato, non per ambienti clinici regolamentati.

---

## 🗂️ Formato `anamnesi.json`

Il file JSON ha questa struttura principale:

```json
{
  "profilo": { ... },
  "farmaci": [ ... ],
  "ordine_categorie": [ "CARDIOLOGIA", "ENDOCRINOLOGIA", ... ],
  "cartelle_drive": { "CARDIOLOGIA": "https://drive.google.com/...", ... },
  "esami": [ ... ]
}
```

Puoi editarlo manualmente o gestirlo interamente dal pannello admin.

---

## 📄 Licenza

Distribuito con licenza **MIT**. Vedi il file [LICENSE](LICENSE) per i dettagli.

---

## ☕ Offrimi un caffè

Se questo strumento ti è stato utile, puoi offrirmi un caffè su PayPal — è il modo più semplice per supportare il progetto!

[![Offrimi un caffè](https://img.shields.io/badge/PayPal-Offrimi%20un%20caff%C3%A8-%2300457C?style=for-the-badge&logo=paypal&logoColor=white)](https://www.paypal.com/paypalme/superscuola)

---

*Realizzato con ❤️ per semplificare la gestione della propria salute.*
