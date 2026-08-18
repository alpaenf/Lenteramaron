# LITERA — AI-Powered Research & Library Navigator

## 1. STATUS PROYEK SAAT INI

Proyek ini merupakan pengembangan lanjutan dari aplikasi yang sebelumnya bernama **LENTERA ILMU**, yaitu sistem informasi pelayanan perpustakaan sekolah berbasis web.

JANGAN membuat aplikasi baru dari nol.

JANGAN menghapus atau merombak fitur existing yang sudah berjalan dengan baik tanpa alasan teknis yang kuat.

Tugas utama adalah mengembangkan aplikasi existing menjadi platform perpustakaan cerdas bernama:

# LITERA

### AI-Powered Research & Library Navigator

Tagline:

> **From Library Knowledge to Research Discovery**

Konsep utama:

> LITERA menghubungkan koleksi pengetahuan lokal yang dimiliki perpustakaan dengan sumber ilmiah eksternal dan perkembangan penelitian terbaru. AI digunakan untuk memahami kebutuhan pengguna, menemukan sumber yang relevan, menjelaskan hubungan antar sumber, dan membantu pengguna menentukan sumber mana yang sebaiknya dipelajari terlebih dahulu.

---

# 2. TUJUAN TRANSFORMASI

Aplikasi existing saat ini sangat kuat pada sisi:

* administrasi perpustakaan
* katalog buku
* data anggota
* peminjaman
* pengembalian
* stok
* pengunjung
* laporan
* dashboard

Namun sistem masih berorientasi pada:

> **Data → CRUD → Transaksi → Laporan**

LITERA harus menambahkan lapisan:

> **Data → AI → Discovery → Insight → Research Navigation**

Tujuannya bukan membuat chatbot biasa.

Tujuannya adalah membuat perpustakaan menjadi **navigator pengetahuan**.

---

# 3. MASALAH YANG INGIN DISELESAIKAN

Pengguna saat ini menghadapi masalah:

1. Informasi akademik terlalu banyak.
2. Pencarian berbasis keyword sering menghasilkan terlalu banyak hasil.
3. Pengguna tidak selalu tahu sumber mana yang paling relevan.
4. Koleksi lokal perpustakaan dan sumber ilmiah eksternal berada pada ekosistem yang terpisah.
5. Pengguna dapat menemukan sumber ilmiah terbaru tetapi tidak selalu memahami hubungan sumber tersebut dengan pengetahuan dasar yang tersedia di perpustakaan.
6. Pengguna sering membutuhkan bantuan untuk menentukan urutan sumber yang sebaiknya dibaca.

LITERA harus menyelesaikan masalah tersebut.

---

# 4. VISI PRODUK

LITERA bukan:

> "Chatbot perpustakaan."

LITERA bukan:

> "Google Scholar versi kecil."

LITERA bukan:

> "Elicit versi sederhana."

LITERA bukan sekadar:

> "AI yang mencari jurnal."

LITERA harus diposisikan sebagai:

> **AI-powered navigator yang menghubungkan koleksi lokal perpustakaan dengan literatur ilmiah eksternal untuk membantu pengguna berpindah dari pengetahuan dasar menuju penelitian terbaru.**

---

# 5. CORE USER FLOW

Contoh pengguna mencari topik:

> "Saya ingin mencari sumber tentang pengaruh Generative AI terhadap pendidikan."

Sistem harus melakukan proses:

```text
User Query
    ↓
AI memahami intent
    ↓
Identifikasi topik dan konsep utama
    ↓
Cari koleksi lokal perpustakaan
    ↓
Cari sumber ilmiah eksternal
    ↓
Gabungkan hasil
    ↓
Deduplicate
    ↓
Relevance Ranking
    ↓
AI menjelaskan relevansi
    ↓
Research Navigation
    ↓
Rekomendasi urutan bacaan
```

---

# 6. NILAI UTAMA LITERA

LITERA memiliki tiga kemampuan utama:

## A. FIND

Membantu pengguna menemukan sumber yang relevan berdasarkan makna pencarian.

Bukan hanya exact keyword matching.

Contoh:

User:

> "Dampak AI generatif terhadap kemampuan belajar mahasiswa."

Sistem dapat menemukan sumber yang menggunakan istilah:

* generative artificial intelligence
* GenAI
* AI-assisted learning
* higher education
* learning performance
* academic achievement

---

## B. UNDERSTAND

Setiap sumber harus dapat diberikan penjelasan singkat:

* mengapa sumber ini relevan
* topik apa yang dibahas
* tingkat relevansinya
* tahun publikasi
* jenis sumber
* keterkaitannya dengan query pengguna

Contoh:

> **Relevansi: 94%**
>
> Sumber ini relevan karena membahas penggunaan Generative AI dalam pendidikan tinggi dan pengaruhnya terhadap proses pembelajaran mahasiswa.

AI tidak boleh mengarang informasi bibliografis.

---

## C. NAVIGATE

Ini merupakan fitur pembeda utama.

LITERA harus membantu pengguna menentukan:

> "Saya harus membaca apa terlebih dahulu?"

Contoh:

### Research Path

```text
01 — Dasar Konsep
        ↓
02 — Penelitian Terdahulu
        ↓
03 — Penelitian Terbaru
        ↓
04 — Topik Spesifik
        ↓
05 — Research Gap / Arah Lanjutan
```

Contoh:

**Langkah 1**
Buku dasar tentang Artificial Intelligence.

**Langkah 2**
Buku atau artikel tentang AI dalam pendidikan.

**Langkah 3**
Jurnal terbaru tentang Generative AI.

**Langkah 4**
Penelitian spesifik mengenai dampaknya terhadap mahasiswa.

Dengan demikian LITERA bukan hanya memberikan daftar sumber tetapi memberikan **jalur eksplorasi pengetahuan**.

---

# 7. SUMBER DATA

LITERA menggunakan dua kategori sumber.

## A. LOCAL LIBRARY KNOWLEDGE

Gunakan database existing dari LENTERA ILMU.

Data yang sudah tersedia antara lain:

* ISBN
* judul buku
* penulis/pengarang
* penerbit
* kategori DDC
* lokasi rak
* stok
* cover
* data anggota
* data sirkulasi

Jangan menghapus sistem existing.

Data buku existing harus menjadi **Local Knowledge Base**.

---

## B. EXTERNAL ACADEMIC SOURCES

Gunakan API atau sumber metadata publik/open access.

Prioritas awal:

### OpenAlex

Untuk:

* pencarian paper
* metadata
* author
* publication
* abstract
* citation information

### Semantic Scholar

Untuk:

* metadata paper
* abstract
* author
* citation
* related papers jika tersedia

### Crossref

Untuk:

* DOI
* metadata bibliografis
* validasi publication metadata

### Unpaywall / DOAJ

Untuk:

* menemukan sumber open-access
* mencari lokasi full text yang legal

JANGAN mengunduh atau menyimpan seluruh internet.

Gunakan retrieval on-demand dan caching.

---

# 8. INTEGRASI AI

Saat ini proyek sudah memiliki **Groq API** yang dapat digunakan untuk kebutuhan LLM.

Jangan mengganti Groq tanpa alasan yang kuat.

Groq digunakan untuk:

* memahami natural language query
* query expansion
* intent extraction
* topical classification
* menjelaskan relevansi sumber
* menghasilkan ringkasan
* membuat research path berdasarkan sumber yang ditemukan

LLM TIDAK boleh menjadi satu-satunya sumber kebenaran.

LLM harus mendapatkan context dari hasil retrieval.

---

# 9. AI GROUNDING

Prinsip:

> **Retrieve First, Generate Second**

Jangan:

```text
User
 ↓
Groq
 ↓
Jawaban
```

Gunakan:

```text
User
 ↓
Query Understanding
 ↓
Retrieval
 ↓
Relevant Sources
 ↓
Groq + Context
 ↓
Answer
```

AI tidak boleh mengarang:

* judul paper
* penulis
* DOI
* tahun
* URL
* isi penelitian
* citation

Jika informasi tidak tersedia dalam data hasil retrieval, AI harus menyatakan bahwa informasi tersebut tidak ditemukan.

---

# 10. SEARCH ENGINE

Tahap awal tidak perlu langsung menggunakan arsitektur terlalu kompleks.

Gunakan pendekatan bertahap.

## Level 1

Keyword search.

## Level 2

Semantic search.

## Level 3

Hybrid search:

```text
Keyword Score
+
Semantic Similarity
+
Recency
+
Citation Signal
```

Hasil akhir harus memiliki ranking.

Contoh:

```text
Overall Relevance Score
= semantic relevance
+ keyword relevance
+ recency score
+ source signal
```

Formula final harus dibuat configurable agar dapat diteliti dan dievaluasi.

---

# 11. FITUR UTAMA YANG AKAN DIBANGUN

## 11.1 AI Research Search

Input:

> "Saya ingin mencari penelitian tentang penggunaan AI untuk meningkatkan literasi siswa."

Output:

* sumber lokal
* jurnal
* artikel
* conference paper
* sumber open access

---

## 11.2 Local + External Discovery

Pisahkan hasil menjadi:

### Koleksi Perpustakaan

Sumber yang tersedia di database lokal.

### Sumber Ilmiah Eksternal

Sumber dari API eksternal.

### Hubungan Sumber

AI menunjukkan hubungan konsep antara keduanya.

Contoh:

> Buku memberikan dasar teori.

> Paper memberikan perkembangan penelitian terbaru.

---

## 11.3 Relevance Explanation

Setiap hasil memiliki:

* relevance score
* alasan relevansi
* topik terkait
* tahun
* tipe sumber

---

## 11.4 Research Path

AI membuat urutan bacaan berdasarkan:

* topik
* relevansi
* level pengetahuan
* tahun
* hubungan antar sumber

---

## 11.5 Save to Research

Pengguna dapat menyimpan sumber.

Data yang dapat disimpan:

* judul
* author
* DOI
* tahun
* source
* URL
* catatan pribadi
* status baca

---

## 11.6 Research Workspace

Pengguna dapat melihat:

* sumber tersimpan
* topik penelitian
* catatan
* sumber lokal
* jurnal
* sumber yang sudah dibaca

Fitur ini harus sederhana.

Jangan membuat reference manager lengkap seperti Zotero.

---

# 12. FITUR YANG TIDAK PERLU DIBANGUN

Jangan memperluas scope menjadi:

* plagiarism checker
* AI paraphraser
* AI grammar checker
* AI essay writer
* full reference manager seperti Zotero
* full systematic review platform seperti Elicit
* citation network platform seperti ResearchRabbit
* generic chatbot

Fokus pada:

> **Find → Understand → Navigate**

---

# 13. EXISTING FEATURES YANG HARUS DIPERTAHANKAN

Jangan menghilangkan:

* landing page
* public book catalog
* guest book
* dashboard
* master book
* master member
* circulation
* borrowing
* return
* stock management
* reports
* Excel import/export
* PDF reports
* RBAC
* system settings

Existing functionality harus tetap berjalan.

---

# 14. ROLE

Untuk sementara pertahankan role existing.

Jika saat ini hanya membutuhkan:

### Admin

Admin bertugas:

* mengelola koleksi
* mengelola metadata
* melakukan import
* mengelola knowledge base
* melihat analytics

Untuk sisi public:

### User / Visitor

Pengguna dapat:

* mencari sumber
* menggunakan AI Research Search
* melihat hasil
* menyimpan sumber

Jangan menambahkan role kompleks jika belum dibutuhkan.

---

# 15. AUTOMATIC BOOK METADATA

Jangan mewajibkan administrator menginput:

* cover
* deskripsi
* metadata
* penulis
* penerbit

satu per satu apabila data dapat ditemukan melalui ISBN.

Buat fitur:

## Metadata Enrichment

```text
ISBN
 ↓
Open Library / Google Books / sumber metadata
 ↓
Metadata
 ↓
Preview
 ↓
Admin Confirm
 ↓
Database
```

ISBN tetap menjadi identifier utama bila tersedia.

Cover bersifat opsional.

---

# 16. USER EXPERIENCE

UI harus tetap modern, ringan, dan profesional.

Jangan membuat tampilan seperti dashboard AI generik.

Pertahankan identitas perpustakaan, tetapi arah visual produk baru harus terasa:

* intelligent
* academic
* modern
* trustworthy
* clean

Focus utama UI:

## Research Search

Bukan chatbot full-screen.

Contoh:

```text
[ Apa yang ingin Anda teliti?                    🔍 ]

Topik:
Generative AI dalam pendidikan tinggi
```

Kemudian:

```text
Recommended Sources

1. Paper A
   Relevance 96%
   2025
   Journal

   Why relevant:
   ...

2. Book B
   Local Library
   Available

   Why relevant:
   ...
```

---

# 17. DATA MODEL BARU

Tambahkan entity jika diperlukan, misalnya:

## ExternalSource

Field contoh:

* id
* external_id
* title
* abstract
* authors
* year
* journal
* doi
* url
* pdf_url
* source_provider
* citation_count
* open_access
* created_at
* updated_at

## SearchQuery

* id
* user_query
* normalized_query
* created_at

## SavedSource

* id
* source_type
* source_id
* notes
* status

## ResearchTopic

* id
* title
* description
* created_by

Jangan membuat migration yang tidak diperlukan.

Ikuti struktur database existing dan konvensi project.

---

# 18. CACHING

Gunakan caching untuk mengurangi API request.

Contoh:

```text
User Query
   ↓
Check Cache
   ├── Found → Use existing data
   └── Not Found
           ↓
        External API
           ↓
        Save Cache
           ↓
        Return Result
```

Jangan melakukan request eksternal berulang untuk query identik jika data masih valid.

---

# 19. API RESILIENCY

External API bisa gagal.

Sistem harus menangani:

* timeout
* rate limit
* empty result
* malformed response
* API unavailable

Jika OpenAlex gagal:

* coba sumber alternatif jika relevan
* atau tampilkan graceful fallback

Jangan membuat aplikasi crash hanya karena external API tidak merespons.

---

# 20. HAL YANG HARUS DIHINDARI

JANGAN:

* menghapus fitur existing
* mengganti framework
* mengganti Laravel
* mengganti Vue
* mengganti database tanpa alasan
* memasukkan AI ke setiap halaman
* membuat chatbot hanya sebagai gimmick
* membuat data palsu jika data nyata tersedia
* mengarang metadata
* membuat klaim ilmiah tanpa sumber
* membuat seluruh sistem bergantung pada Groq

---

# 21. TARGET MVP

Versi pertama cukup memiliki:

### MVP 1

AI Research Search

### MVP 2

Local + External Source Discovery

### MVP 3

Relevance Ranking

### MVP 4

Explainable Recommendation

### MVP 5

Research Path

### MVP 6

Save to Research

Jangan membangun semua fitur lanjutan sebelum MVP berjalan stabil.

---

# 22. EVALUASI

Sistem harus dirancang supaya nantinya bisa dievaluasi.

Metrik yang mungkin digunakan:

* Precision
* Recall
* Precision@K
* Recall@K
* MRR
* NDCG
* response time
* usability

Untuk eksperimen, bandingkan:

### Baseline

Keyword Search

vs.

### Proposed Method

Hybrid/Semantic Search

Tujuan:

> mengetahui apakah pendekatan AI-assisted retrieval menghasilkan sumber yang lebih relevan.

Jangan melakukan klaim bahwa sistem lebih baik sebelum pengujian dilakukan.

---

# 23. ARAH INOVASI

Inovasi utama LITERA bukan:

> "AI dapat mencari jurnal."

Itu sudah banyak dilakukan oleh berbagai platform.

Inovasi yang ingin dibangun adalah:

> **Menghubungkan koleksi pengetahuan lokal perpustakaan dengan sumber ilmiah eksternal dan memanfaatkan AI untuk membantu pengguna berpindah dari pengetahuan dasar menuju literatur terbaru.**

Dengan konsep:

```text
Local Library
      ↓
Knowledge Foundation
      ↓
External Research
      ↓
AI Relevance Analysis
      ↓
Research Navigation
```

---

# 24. POSITIONING PRODUK

Nama:

# LITERA

Sub-title:

### AI-Powered Research & Library Navigator

Tagline:

> **From Library Knowledge to Research Discovery**

One-liner:

> LITERA membantu pengguna menemukan, memahami, dan menavigasi sumber pengetahuan dengan menghubungkan koleksi perpustakaan lokal dan literatur ilmiah terbaru menggunakan AI.

---

# 25. INSTRUKSI KHUSUS UNTUK AI CODING ASSISTANT

Sebelum menulis kode:

1. Baca seluruh struktur project.
2. Baca README.
3. Baca PRD.
4. Baca architecture.
5. Baca feature documentation.
6. Identifikasi database schema.
7. Identifikasi existing API.
8. Identifikasi modul yang sudah stabil.
9. Jangan mengubah bagian existing tanpa alasan.
10. Buat rencana perubahan sebelum implementasi.

Setelah memahami project:

### Tahap 1

Audit existing architecture.

### Tahap 2

Tentukan integration points.

### Tahap 3

Buat database migration yang diperlukan.

### Tahap 4

Buat service layer untuk external academic APIs.

### Tahap 5

Buat retrieval/search layer.

### Tahap 6

Integrasikan Groq.

### Tahap 7

Buat Research Search UI.

### Tahap 8

Buat relevance explanation.

### Tahap 9

Buat Research Path.

### Tahap 10

Testing dan optimization.

Setiap perubahan harus mempertahankan compatibility dengan fitur existing.

---

# 26. HASIL AKHIR YANG DIHARAPKAN

Setelah transformasi selesai, aplikasi harus terasa sebagai evolusi dari perpustakaan tradisional menjadi intelligent knowledge platform.

Bukan sekadar:

> "Perpustakaan + AI chatbot."

Tetapi:

> **"Perpustakaan yang dapat menghubungkan pengetahuan yang dimiliki dengan pengetahuan yang sedang berkembang."**

Core loop:

```text
Search
 ↓
Discover
 ↓
Understand
 ↓
Connect
 ↓
Navigate
 ↓
Save
```

Semua fitur yang dibangun harus mendukung core loop tersebut.

---

# 27. CATATAN PENELITIAN

Produk ini juga dirancang agar dapat menjadi dasar eksperimen akademik.

Topik penelitian potensial:

> Perbandingan keyword search dan semantic/hybrid search dalam menemukan sumber ilmiah yang relevan.

atau:

> Evaluasi efektivitas AI-assisted research navigation dalam membantu pengguna menemukan sumber penelitian.

Karena itu, implementation harus dibuat modular dan dapat diuji.

Jangan menggabungkan seluruh logic retrieval ke dalam controller.

Gunakan service/module terpisah agar algoritma dapat diganti dan dibandingkan.

---

# 28. PRINCIPLE UTAMA

Selalu prioritaskan:

**Existing functionality > stability > data correctness > AI quality > visual novelty**

Dan:

> **AI harus menyelesaikan masalah, bukan sekadar menjadi fitur karena proyek membutuhkan AI.**
