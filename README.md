# Earnings Call Viewer

ระบบแสดงสรุป Earnings Call สำหรับนักลงทุน ประกอบด้วย 3 ไฟล์หลัก

---

## ไฟล์ในระบบ

### `index.php`
Redirect ไปยัง `sum-list.php` อัตโนมัติ

### `sum-list.php`
หน้าหลักสำหรับผู้ใช้งาน — แสดงรายชื่อหุ้นทั้งหมดในโฟลเดอร์เป็น chip  
กดที่ chip แล้วเปิดอ่านสรุปได้เลย

### `filelist.php`
File browser สำหรับผู้ดูแลระบบ — แสดงไฟล์และโฟลเดอร์ทั้งหมด  
ดูขนาดไฟล์ วันที่แก้ไข และ export รายชื่อเป็น CSV ได้

---

## โครงสร้างโฟลเดอร์

```
/web/earncall/
├── 2026q1/
│   ├── index.php         ← redirect
│   ├── sum-list.php      ← หน้าหลัก (chips)
│   ├── filelist.php      ← file browser
│   ├── ADVANC_2026Q1_EarningCallSummary.md
│   ├── BEM_2026Q1_EarningCallSummary.md
│   └── ...
└── 2025q4/
    ├── index.php
    ├── sum-list.php
    ├── filelist.php
    └── ...
```

---

## การตั้งชื่อไฟล์ .md

ระบบสกัดชื่อหุ้นจากส่วนแรกก่อน `_` เสมอ

```
ADVANC_2026Q1_EarningCallSummary.md  →  chip: ADVANC
88TH_2026Q1_EarningCallSummary.md   →  chip: 88TH
```

ไฟล์ที่ขึ้นต้นด้วยตัวเลขจะถูกจัดอยู่ใน section **0-9** ก่อน section A-Z

---

## การ deploy โฟลเดอร์ใหม่ (เช่น 2027q1)

1. สร้างโฟลเดอร์ใหม่ เช่น `/web/earncall/2027q1/`
2. คัดลอก `index.php`, `sum-list.php`, `filelist.php` เข้าไป
3. วางไฟล์ `.md` ในโฟลเดอร์นั้น
4. เข้าใช้งานได้ที่ `https://www.mrlikestock.com/web/earncall/2027q1/`

ไม่ต้องแก้ไขโค้ดใดๆ — ระบบอ่านชื่อโฟลเดอร์และไฟล์อัตโนมัติ

---

## URL ที่ใช้งาน

| หน้า | URL |
|---|---|
| หน้าหลัก (chips) | `/web/earncall/2026q1/` |
| File browser | `/web/earncall/2026q1/filelist.php` |
| อ่านไฟล์ .md | `/web/share/notes.php?url=...` |

---

## Tech Stack

- PHP 5+ (ไม่ใช้ฟีเจอร์ PHP 7+)
- ไม่มี dependency / ไม่ต้องติดตั้ง library
- CSS ฝังในไฟล์เดียว รองรับ Dark mode และ Mobile
