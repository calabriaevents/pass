# 🎯 **SETUP MYSQL per Passione Calabria**

Se preferisci usare **MySQL con phpMyAdmin** invece di SQLite, segui questa guida!

## 📂 **File Creati per MySQL**

- ✅ **`database_mysql.sql`** - File da importare in phpMyAdmin
- ✅ **`includes/database_mysql.php`** - Classe Database per MySQL
- ✅ **`setup_mysql.md`** - Questa guida

---

## 🚀 **PROCEDURA SETUP MYSQL**

### **1. Crea Database in phpMyAdmin**
```sql
1. Accedi a phpMyAdmin
2. Clicca "Nuovo" per creare database
3. Nome: "passione_calabria"
4. Collation: "utf8mb4_unicode_ci"
5. Clicca "Crea"
```

### **2. Importa Struttura e Dati**
```sql
1. Seleziona il database "passione_calabria"
2. Vai su tab "Importa"
3. Scegli file "database_mysql.sql"
4. Clicca "Esegui"
5. ✅ FATTO! Struttura completa importata
```

### **3. Configura Connessione**
Apri `includes/database_mysql.php` e modifica:

```php
class Database {
    public function __construct() {
        // 🔧 MODIFICA QUESTI VALORI
        $this->host = 'localhost';        // Server MySQL
        $this->dbname = 'passione_calabria'; // Nome database
        $this->username = 'root';         // Username MySQL
        $this->password = '';             // Password MySQL
```

### **4. Sostituisci File Database**
```bash
# Rinomina i file:
mv includes/database.php includes/database_sqlite.php
mv includes/database_mysql.php includes/database.php
```

### **5. Testa Connessione**
```php
// Vai su: http://tuosito.com/index.php
// Se vedi la homepage = FUNZIONA! ✅
```

---

## 📊 **COSA CONTIENE IL DATABASE**

### **Struttura Completa**
- ✅ **15 tabelle** con relazioni
- ✅ **Indici ottimizzati** per performance
- ✅ **Foreign keys** per integrità
- ✅ **UTF8MB4** per emoji e caratteri speciali

### **Dati Precaricati**
- ✅ **18 categorie** complete con icone
- ✅ **5 province** calabresi
- ✅ **13 città** principali con coordinate GPS
- ✅ **5 articoli** di esempio
- ✅ **3 pacchetti** business
- ✅ **5 pagine** statiche (chi siamo, privacy, ecc.)
- ✅ **Impostazioni** sistema

---

## ⚡ **VANTAGGI MYSQL vs SQLite**

### **✅ Vantaggi MySQL**
- 🔧 **phpMyAdmin** per gestione grafica
- 🔧 **Backup** semplici via phpMyAdmin
- 🔧 **Utenti multipli** e permessi
- 🔧 **Query analyzer** avanzato
- 🔧 **Replication** per scalabilità

### **❌ Svantaggi MySQL**
- 🔧 **Setup più complesso** (server MySQL)
- 🔧 **Risorse maggiori** (RAM/CPU)
- 🔧 **Configurazione** server necessaria

---

## 🛠️ **CONFIGURAZIONI HOSTING**

### **Hosting Condiviso**
```php
// Valori tipici hosting condiviso:
$this->host = 'localhost';
$this->dbname = 'tuoaccount_passionecalabria';
$this->username = 'tuoaccount_dbuser';
$this->password = 'password_sicura';
```

### **VPS/Server Dedicato**
```php
// Configurazione VPS:
$this->host = 'localhost'; // o IP server
$this->dbname = 'passione_calabria';
$this->username = 'calabria_user';
$this->password = 'password_complessa';
```

---

## 🔧 **TROUBLESHOOTING**

### **Errore Connessione**
```
❌ SQLSTATE[HY000] [1045] Access denied
🔧 Soluzione: Verifica username/password MySQL
```

### **Database Non Trovato**
```
❌ SQLSTATE[HY000] [1049] Unknown database
🔧 Soluzione: Crea database "passione_calabria" in phpMyAdmin
```

### **Tabelle Non Esistono**
```
❌ Table 'passione_calabria.articles' doesn't exist
🔧 Soluzione: Importa file database_mysql.sql
```

### **Caratteri Strani**
```
❌ Caratteri come ??? o quadratini
🔧 Soluzione: Imposta charset utf8mb4 nel database
```

---

## 📱 **GESTIONE BACKUP MYSQL**

### **Backup Automatico**
```php
// Il sistema crea backup via mysqldump
public function createBackup() {
    $command = "mysqldump --host={$this->host} --user={$this->username} --password={$this->password} {$this->dbname}";
    return shell_exec($command);
}
```

### **Backup Manuale phpMyAdmin**
```sql
1. Vai in phpMyAdmin
2. Seleziona database "passione_calabria"
3. Tab "Esporta"
4. Formato: SQL
5. Download file .sql
```

---

## ⚖️ **SCELTA: SQLite vs MySQL**

### **Usa SQLite se:**
- ✅ Hosting semplice/economico
- ✅ Progetto piccolo/medio
- ✅ Zero configurazione
- ✅ Backup file semplici

### **Usa MySQL se:**
- ✅ Progetto grande/enterprise
- ✅ Team multipli
- ✅ Integrazioni complesse
- ✅ phpMyAdmin preferito

---

## 🎯 **RISULTATO FINALE**

Con **MySQL configurato** otterrai:

- 🎯 **Stesse funzionalità** del progetto SQLite
- 🎯 **Gestione grafica** via phpMyAdmin
- 🎯 **Performance** identiche per progetti medi
- 🎯 **Compatibilità** hosting tradizionale
- 🎯 **Backup** più familiari per amministratori

**Il progetto funziona identicamente con SQLite o MySQL!** 🚀

---

## 📞 **Supporto**

Se hai problemi con la configurazione MySQL:

1. **Controlla** credenziali database
2. **Verifica** che MySQL sia avviato
3. **Testa** connessione da terminale
4. **Consulta** log errori PHP
5. **Usa** SQLite se MySQL è complesso

**La scelta tra SQLite e MySQL non cambia le funzionalità del progetto!**
