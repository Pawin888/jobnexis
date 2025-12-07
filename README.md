# 🛠️ My Laravel Project (สำหรับ Ubuntu)

ยินดีต้อนรับเข้าสู่โปรเจกต์ Laravel!  
ไฟล์นี้จะช่วยให้คุณสามารถรันเว็บนี้บนเครื่องของคุณได้ แม้คุณจะไม่เคยเขียนโค้ดมาก่อน 💡

---

## 📋 สิ่งที่ต้องเตรียมก่อนเริ่ม

### ✅ ติดตั้งโปรแกรมที่จำเป็น

1. เปิด Terminal แล้วรันคำสั่งนี้ทีละบรรทัด:

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install git curl -y
```

2. ติดตั้ง Docker (รันทีเดียว)

```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
newgrp docker
```

> ⚠️ ถ้าเคยลง Docker แล้ว ข้ามขั้นตอนนี้ได้เลย

---

## 📦 ดาวน์โหลดโปรเจกต์นี้

1. เปิด Terminal แล้วเข้าไปยังโฟลเดอร์ที่ต้องการเก็บโปรเจกต์ เช่น Desktop หรือ Documents

```bash
cd ~/Desktop
```

2. ดาวน์โหลดโปรเจกต์นี้จาก GitHub (ใส่ลิงก์จริงของโปรเจกต์คุณ)

```bash
git clone https://github.com/yourteam/yourproject.git
cd yourproject
```

---

## ⚙️ ตั้งค่าโปรเจกต์

1. สร้างไฟล์ `.env` โดยใช้ไฟล์ตัวอย่าง:

```bash
cp .env.example .env
```

2. เรียกใช้ Laravel Sail (Docker)

```bash
./vendor/bin/sail up -d
```

3. สั่งติดตั้งระบบฐานข้อมูล:

```bash
./vendor/bin/sail artisan migrate
```

4. สร้างข้อมูลตัวอย่าง (Optional):

```bash
./vendor/bin/sail artisan db:seed
```

---

## 🚀 เปิดเว็บเพื่อดูผลงาน

ตอนนี้เว็บเปิดอยู่ที่:

-   👉 http://localhost — หน้าเว็บหลัก
-   📬 http://localhost:8025 — ทดสอบการส่งอีเมล (Mailpit)

เปิดใน Google Chrome ได้เลย!

---

## 📚 คำสั่งที่ใช้บ่อย

| คำสั่ง                              | ใช้ทำอะไร                |
| ----------------------------------- | ------------------------ |
| `./vendor/bin/sail up -d`           | เริ่มต้นระบบ (เปิดเว็บ)  |
| `./vendor/bin/sail down`            | ปิดระบบ                  |
| `./vendor/bin/sail artisan migrate` | สร้างตารางในฐานข้อมูล    |
| `./vendor/bin/sail artisan db:seed` | สร้างข้อมูลจำลอง         |
| `./vendor/bin/sail artisan`         | คำสั่งทั้งหมดของ Laravel |

---

## ❓ คำถามที่พบบ่อย (FAQ)

**Q: เปิดเว็บแล้วขึ้น error หรือไม่โหลด?**  
🔹 ตรวจสอบว่าได้รันคำสั่ง `./vendor/bin/sail up -d` แล้วหรือยัง  
🔹 รีสตาร์ทเครื่องแล้วลองใหม่ก็ช่วยได้

**Q: ต้องใส่รหัสผ่าน `sudo` ไหม?**  
🔹 ใช่ ตอนติดตั้ง Docker ครั้งแรกจะมีถาม

**Q: ลืมคำสั่งที่ต้องใช้?**  
🔹 เปิด README นี้ แล้ว copy ได้เลย

---

## 🙋 ติดต่อ

หากมีปัญหา รบกวนติดต่อ:

-   คุณ [ชัยมนัส แอบสุข] – Developer
-   Discord: [421948474893008897]
-   หรือแจ้งใน Group Line : ESP:นิสิตสหกิจก็ได้

---

> ขอให้สนุกกับการใช้งานครับ 😊
