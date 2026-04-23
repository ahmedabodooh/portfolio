# نشر الموقع على cPanel (ahmed-abo-dooh.site)

دليل خطوة بخطوة لرفع البرتفوليو على الاستضافة.

## الترتيب مش بيتغير ✓
هنرفع الـLaravel project كامل زي ما هو. مش محتاج تحرّك أي ملف بين مجلدات.
الحاجة الوحيدة اللي بتتغيّر على السيرفر هي الـ**Document Root** — بنقوله "الـroot بتاع
الدومين هو فولدر `public/` جوه الـapp"، مش الفولدر الأب.

---

## قبل ما ترفع — حضّر الـzip على جهازك

من فولدر `portfolio/` اعمل zip واحد يحتوي كل الآتي:

**ضمّن:**
```
app/
bootstrap/
config/
database/
frontend/
public/
resources/
routes/
storage/
vendor/            ← مهم! ارفعه (cPanel غالباً مفيش عنده composer)
tests/             ← اختياري
.editorconfig
.env.example
.gitattributes
.htaccess          (لو موجود في الـroot)
artisan
composer.json
composer.lock
README.md
```

**مـ**ـا تضمّنش:**
- `.env`          ← هتعمله جديد على السيرفر
- `.git/`         ← مش محتاج
- `node_modules/` ← مفيش عندك أصلاً
- `storage/logs/*.log`
- `storage/framework/cache/data/*`
- `storage/framework/sessions/*`
- `storage/framework/views/*`
- `public/storage` (الـsymlink — لو Windows هيكسر. هنعمله على السيرفر)

> **نصيحة:** لو حجم الـvendor/ كبير للرفع، سيبه واستخدم composer على السيرفر (لو متاح).

---

## 1. ارفع الـzip وفكّه

1. ادخل cPanel → **File Manager**
2. روح للـpath: `/home/reconnectinvestm/ahmed-abo-dooh.site/`
3. امسح الفولدرات اللي جواه (`.well-known`, `cgi-bin`) — **لا**، سيبهم (`.well-known` مهم للـSSL)
4. اضغط **تحميل (Upload)** وارفع الـzip
5. بعد ما يخلص، ارجع للـFile Manager → right-click على الـzip → **Extract**
6. بعد الاستخراج، المفروض تلاقي كل الفولدرات (app, bootstrap, public, ...) جوه
   `/home/reconnectinvestm/ahmed-abo-dooh.site/`

---

## 2. غيّر الـDocument Root

هذه أهم خطوة — Laravel لازم يشتغل من `public/`:

1. cPanel → **Domains** → جنب `ahmed-abo-dooh.site` اضغط **Manage**
2. تحت **UPDATE THE DOMAIN** → **New Document Root**
3. غيّر القيمة من:
   ```
   ahmed-abo-dooh.site
   ```
   إلى:
   ```
   ahmed-abo-dooh.site/public
   ```
4. اضغط **تحديث / Update**

> لو cPanel ما سمحش بتغيير الـDocument Root (نادر)، فيه حل بديل
> تحت — "Option B".

---

## 3. اعمل قاعدة بيانات MySQL

1. cPanel → **MySQL Databases**
2. اعمل **Database جديدة**: مثلاً `reconnectinvestm_portfolio`
3. اعمل **User جديد**: مثلاً `reconnectinvestm_portuser` بكلمة مرور قوية
4. تحت **Add User To Database**: اضيف الـuser للـdatabase بـ**ALL PRIVILEGES**
5. احفظ الاسم الكامل (`reconnectinvestm_portfolio`) والـuser والباسورد — هتحتاجهم

---

## 4. اعمل ملف `.env` على السيرفر

1. File Manager → افتح `/home/reconnectinvestm/ahmed-abo-dooh.site/`
2. right-click → **ملف جديد** → اسمه `.env`
3. افتحه بـEdit وحط المحتوى ده:

```ini
APP_NAME="Ahmed Abo Dooh"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://ahmed-abo-dooh.site

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reconnectinvestm_portfolio
DB_USERNAME=reconnectinvestm_portuser
DB_PASSWORD=كلمة-المرور-هنا

SESSION_DRIVER=database
SESSION_LIFETIME=120

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log

SANCTUM_STATEFUL_DOMAINS=ahmed-abo-dooh.site,www.ahmed-abo-dooh.site
```

غيّر بس `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` للقيم اللي عملتها في الخطوة 3.

---

## 5. شغّل أوامر Laravel عبر الـTerminal

1. cPanel → **Terminal** (لو مش ظاهر، ممكن يكون محجوب على باقات معينة —
   لو الحالة دي استخدم Option B)
2. نفّذ الأوامر دي بالترتيب:

```bash
cd /home/reconnectinvestm/ahmed-abo-dooh.site

# تأكد من نسخة PHP (لازم 8.2+)
php -v

# ولّد مفتاح الـapp
php artisan key:generate

# شغّل الـmigrations
php artisan migrate --force

# seed الـdata الأولية (projects, settings, admin user)
php artisan db:seed --class=PortfolioSeeder --force

# اعمل symlink من public/storage → storage/app/public
php artisan storage:link

# احسب الـcache (اختياري لكن بيسرّع الموقع)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# permissions
chmod -R 775 storage bootstrap/cache
```

---

## 6. تأكد من إعدادات PHP

cPanel → **MultiPHP Manager** (أو **Select PHP Version**):

- **PHP Version:** 8.2 أو 8.3
- تحت **Extensions** فعّل:
  - `intl` (لازم)
  - `pdo_mysql`
  - `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `bcmath`, `curl`, `json`, `gd` (معظمهم by default)

---

## 7. جرّب الموقع

افتح `https://ahmed-abo-dooh.site/` — المفروض تشوف البرتفوليو.

**اختبر:**
- [ ] الصفحة الرئيسية بتحمل
- [ ] `/admin/login` بتفتح → login بـ:
  - Email: `zalfyhima@gmail.com`
  - Password: `password` (**غيّره فوراً** من Settings)
- [ ] `/api/v1/site/profile` بترجع JSON
- [ ] رفع صورة من الأدمن → بتظهر في الموقع (دليل إن الـstorage:link شغّال)

---

## لو حصلت مشكلة

### الموقع بيطلع 500 Internal Server Error
1. شغّل `chmod -R 775 storage bootstrap/cache`
2. افحص `storage/logs/laravel.log` من File Manager
3. تأكد PHP ≥ 8.2 في MultiPHP Manager

### الصور اللي رفعتها من الأدمن مش بتظهر
- تأكد إن `php artisan storage:link` اشتغل
- تحقق إن `public/storage` موجود وبيشاور على `../storage/app/public`

### الـdatabase بيدّيك "Connection refused"
- اتأكد من `DB_HOST=127.0.0.1` (مش `localhost` — cPanel أحياناً بيفرّق)
- اتأكد اسم الـdatabase والـuser فيهم البريفكس `reconnectinvestm_`

### لما تعدّل `.env` وميظهرش التغيير
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Option B — لو Document Root مش قابل للتغيير

لو cPanel مانعك تغيّر الـdocument root:

1. ارفع الـapp في فولدر **خارج** الـweb root:
   ```
   /home/reconnectinvestm/portfolio-app/     ← الـapp كامل
   ```
2. انقل **محتويات** `public/` (مش الفولدر نفسه) لـ:
   ```
   /home/reconnectinvestm/ahmed-abo-dooh.site/
   ```
3. عدّل `ahmed-abo-dooh.site/index.php` يشاور على الـapp:
   ```php
   require __DIR__.'/../portfolio-app/vendor/autoload.php';
   $app = require_once __DIR__.'/../portfolio-app/bootstrap/app.php';
   ```
4. اعمل storage link يدوي:
   ```
   cd /home/reconnectinvestm/ahmed-abo-dooh.site
   ln -s ../portfolio-app/storage/app/public storage
   ```

الـOption A أسهل بكتير. جرّبها الأول.

---

## تحديثات لاحقة

لما تعدّل حاجة في الكود محلياً وعايز ترفعها:

1. zip الملفات اللي اتغيّرت بس
2. ارفعها بالـFile Manager (replace)
3. لو عدّلت migrations → `php artisan migrate --force`
4. لو عدّلت config/routes/views → `php artisan config:clear && php artisan route:clear && php artisan view:clear`
