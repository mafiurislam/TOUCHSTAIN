# TOUCHSTAINS — Laravel SQLite GitHub & Live Hosting Deployment Guide
### গিটহাবে আপলোড এবং হোস্টিং সার্ভারে লাইভ ডেপ্লয়মেন্ট গাইড

এই প্রজেক্টটি সম্পূর্ণ **PHP Laravel 11 + SQLite** আর্কিটেকচারে তৈরি করা হয়েছে। এটি যেকোনো হোস্টিং সার্ভারে (cPanel, Hostinger, Namecheap, VPS, Ploi, Forge) **কোনো মাইগ্রেশন ত্রুটি বা ডেটাবেস কনফিগারেশন সমস্যা ছাড়াই** সহজে লাইভ করার জন্য সেলফ-হিলিং (Self-Healing) অটো-সেটআপ দিয়ে প্রস্তুত করা হয়েছে।

---

## 🌟 প্রজেক্টে যা যা বিল্ট-ইন রয়েছে (Key Features)

1. **ফুল কালারফুল রিডিজাইন (Vibrant Theme)**:
   - সফট পেস্টেল অ্যাম্বিয়েন্ট ব্যাকগ্রাউন্ড মেশ গ্রেডিয়েন্ট (সাদা ব্যাকগ্রাউন্ড পরিবর্তন করে আকর্ষণীয় কালার দেওয়া হয়েছে)।
   - কালারফুল ক্যাটাগরি সার্কেল রিংস (Coral, Amber, Cyan, Emerald, Purple, Magenta)।
   - ৬টি লাইভ ডেমো ভিডিও সেকশন কালারফুল ব্যাজ ও কার্ড ব্যাকড্রপ সহ।
   - প্রিমিয়াম প্রোডাক্ট কার্ড, গ্লোয়িং ডিসকাউন্ট পিল এবং ট্রাস্ট ও ভ্যালু হাইলাইট বার।
2. **AU Small Finance Bank ম্যানুয়াল ব্যাংক ট্রান্সফার পেমেন্ট সিস্টেম**:
   - মার্চেন্ট অ্যাকাউন্ট ডিটেইলস (ZOYA ENTERPRISES, A/C: 2502266564968702, IFSC: AUBL0002665)।
   - চেকআউটে ও অর্ডার হিস্ট্রিতে ব্যাংক কার্ড + সিঙ্গেল-ক্লিক কপি বাটন।
   - কাস্টমার UTR নম্বর এবং অপশনাল স্ক্রিনশট সাবমিট ফর্ম।
   - অ্যাডমিন ড্যাশবোর্ড (`/admin`): রিয়েল-টাইম পেমেন্ট ভেরিফিকেশন কিউ, অ্যাপ্রুভ/রিজেক্ট/নোট এবং ব্যাংক অ্যাকাউন্ট সেটিংস এডিটর।
3. **সেলফ-হিলিং ডিপ্লয়মেন্ট ইঞ্জিন (Zero-Config Deployment)**:
   - রুট `.htaccess` এবং রুট `index.php` বিল্ট-ইন — cPanel বা Shared Hosting এ `public/` ডিরেক্টরি ডকুমেন্ট রুট না করলেও সাইট ১০০% স্মুথলি চলবে।
   - SQLite অটো-ইনিশিয়ালাইজেশন — সার্ভারে `database.sqlite` মিসিং থাকলে ব্যাকআপ ফাইল `database.sqlite.default` থেকে অটো-রিস্টোর হবে।
   - অটোমেটিক স্টোরেজ ডিরেক্টরি ক্রিয়েশন ও `.gitkeep` ফাইলস।

---

## 🚀 ধাপ ১: গিটহাবে আপলোড করার নিয়ম (Push to GitHub)

### টার্মিনাল বা কমান্ড প্রম্পট খুলুন:
পিসিতে এই ফোল্ডারে টার্মিনাল খুলুন:
`c:\Users\SIMRAN\OneDrive\Desktop\getgozo\getgozo`

### নিচের কমান্ডগুলো একে একে রান করুন:

```bash
# ১. গিট ইনিশিয়ালাইজ করুন
git init

# ২. সমস্ত ফাইল স্টেজিংয়ে অ্যাড করুন
git add .

# ৩. প্রথম কমিট করুন
git commit -m "TOUCHSTAINS complete Laravel SQLite with AU Bank Transfer & Colorful Theme"

# ৪. মেইন ব্রাঞ্চ নির্বাচন করুন
git branch -M main

# ৫. আপনার GitHub রিপোজিটরি লিংক অ্যাড করুন (আপনার গিটহাব লিংক দিন)
git remote add origin https://github.com/YOUR_GITHUB_USERNAME/YOUR_REPOSITORY_NAME.git

# ৬. গিটহাবে পুশ করুন
git push -u origin main
```

*(যদি আগে থেকেই রিপোজিটরি লিংক অ্যাড করা থাকে এবং শুধু আপডেট করতে চান, তবে `git add .`, `git commit -m "Update"`, এবং `git push origin main` দিলেই হবে।)*

---

## 🌐 ধাপ ২: হোস্টিং সার্ভারে ডেপ্লয়মেন্ট (cPanel / Shared Hosting)

### পদ্ধতি A: cPanel Git Version Control (সবচেয়ে সহজ - ৩ ক্লিকে লাইভ)

1. আপনার হোস্টিংয়ের **cPanel**-এ লগইন করুন।
2. **Files** সেকশনে গিয়ে **Git™ Version Control** এ ক্লিক করুন।
3. **Create** বাটনে ক্লিক করুন:
   - **Clone URL**: আপনার গিটহাব রিপোজিটরি URL দিন (e.g., `https://github.com/username/touchstains.git`)
   - **Repository Path**: আপনার ডোমেনের জন্য নির্ধারিত পাথ দিন, যেমন: `public_html` (অথবা সাবডোমেনের জন্য `public_html/subfolder`)
   - **Repository Name**: touchstains
4. **Create** বাটনে ক্লিক করুন। cPanel স্বয়ংক্রিয়ভাবে গিটহাব থেকে সমস্ত ফাইল ডাউনলোড করে দেবে!
5. **ব্যাস! আপনার ওয়েবসাইট সাথে সাথে লাইভ হয়ে যাবে!** 
   - আমাদের তৈরিকৃত রুট `.htaccess` এবং `public/index.php` স্বয়ংক্রিয়ভাবে সার্ভারের জন্য প্রস্তুত হয়ে যাবে।

---

### পদ্ধতি B: File Manager দিয়ে আপলোড (বিকল্প পদ্ধতি)

1. প্রজেক্টের সমস্ত ফাইল সিলেক্ট করে একটি `touchstains.zip` তৈরি করুন (`vendor` ফোল্ডার সহ)।
2. cPanel-এর **File Manager**-এ যান এবং `public_html` ফোল্ডারে ফাইলটি আপলোড করে **Extract** করুন।
3. ব্রাউজারে আপনার ডোমেন ভিজিট করুন (e.g. `https://yourdomain.com`).

---

### পদ্ধতি C: VPS বা Cloud Server (Ubuntu / Nginx / Apache)

যদি আপনি VPS বা Cloud সার্ভার ব্যবহার করেন:

```bash
# ১. সার্ভারে ডিরেক্টরিতে যান
cd /var/www

# ২. রিপোজিটরি ক্লোন করুন
git clone https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git touchstains
cd touchstains

# ৩. ফাইল ও ফোল্ডার পারমিশন সেট করুন
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R 775 storage bootstrap/cache database

# ৪. অ্যাপ ক্যাশ ক্লিয়ার করুন (ঐচ্ছিক)
php artisan optimize:clear
```

---

## ⚙️ ফাইল পারমিশন (Permissions Check)

শেয়ার্ড হোস্টিং বা cPanel-এ নিচের ফোল্ডারগুলোর রাইট পারমিশন (Write Permission) নিশ্চিত করুন:
- `storage/` -> `0775` অথবা `0755`
- `bootstrap/cache/` -> `0775` অথবা `0755`
- `database/` -> `0775` অথবা `0755`
- `database/database.sqlite` -> `0664` অথবা `0644`

*(আমাদের সেলফ-হিলিং স্ক্রিপ্ট এটি রানটাইমে স্বয়ংক্রিয়ভাবে চেক করে তৈরি করে নেয়।)*

---

## 🔒 অ্যাডমিন প্যানেল এবং AU Bank Transfer ম্যানেজমেন্ট

- **অ্যাডমিন ড্যাশবোর্ড URL**: `https://yourdomain.com/admin`
- **সুবিধাসমূহ**:
  1. **Bank Transfer Verification Queue**: গ্রাহকদের পাঠানো UTR নম্বর এবং জমা দেওয়া পেমেন্ট রসিদ দেখে এক ক্লিকে **Approve** অথবা **Reject** (কারণ সহ) করা যায়।
  2. **Bank Account Settings**: ZOYA ENTERPRISES এর AU Small Finance Bank অ্যাকাউন্ট নম্বর, IFSC কোড, অ্যাকাউন্ট হোল্ডারের নাম এবং ব্রাঞ্চ অ্যাড্রেস পরিবর্তন করার সম্পূর্ণ কন্ট্রোল প্যানেল।
  3. **Order Status Tracking**: রিয়েলটাইমে অর্ডার স্ট্যাটাস পেন্ডিং থেকে কনফার্মড এবং ডেলিভারিতে পরিবর্তন।

---

## 🛠️ লোকাল প্রিভিউ বা টেস্টিং (Local Preview)

লোকালে টেস্ট করার জন্য টার্মিনালে রান করুন:
```bash
php -S 127.0.0.1:8080 router.php
```
এরপর ব্রাউজারে ভিজিট করুন:
- **হোমপেজ**: `http://127.0.0.1:8080/`
- **চেকআউট**: `http://127.0.0.1:8080/checkout`
- **অর্ডারস**: `http://127.0.0.1:8080/orders`
- **অ্যাডমিন প্যানেল**: `http://127.0.0.1:8080/admin`
