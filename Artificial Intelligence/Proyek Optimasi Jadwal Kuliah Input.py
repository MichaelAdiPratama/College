import random
import pandas as pd
from tkinter import *
from tkinter import messagebox
import datetime as dt
# Inisialisasi jadwal kuliah
jadwal_kuliah = []

# Fungsi untuk menambahkan jadwal kuliah
def tambah_jadwal():
    jam_mulai = int(jam_mulai_entry.get())
    jam_selesai = int(jam_selesai_entry.get())
    nama_dosen = nama_dosen_entry.get()
    mata_kuliah = mata_kuliah_entry.get()
    sks = int(sks_entry.get())
    ruangan = ruangan_entry.get()
    hari = hari_var.get()
    jadwal_kuliah.append([hari,mata_kuliah,nama_dosen,jam_mulai, jam_selesai, ruangan,sks])
    jadwal_df = pd.DataFrame(jadwal_kuliah, columns=["Hari", "Mata Kuliah","Nama Dosen", "Jam Mulai", "Jam Selesai", "Ruangan", "SKS"])
    jadwal_text.delete(1.0, END)
    jadwal_text.insert(END, jadwal_df.to_string(index=False))

# Fungsi untuk menghapus jadwal kuliah terakhir
def hapus_jadwal():
    if jadwal_kuliah:
        jadwal_kuliah.pop()
        jadwal_df = pd.DataFrame(jadwal_kuliah, columns=["Hari", "Mata Kuliah","Nama Dosen", "Jam Mulai", "Jam Selesai", "Ruangan", "SKS"])
        jadwal_text.delete(1.0, END)
        jadwal_text.insert(END, jadwal_df.to_string(index=False))
    else:
        messagebox.showinfo("Peringatan", "Tidak ada jadwal kuliah yang dapat dihapus.")

# Inisialisasi populasi awal
def inisialisasi_populasi(jumlah_individu):
    populasi = []
    for _ in range(jumlah_individu):
        individu = random.sample(jadwal_kuliah, len(jadwal_kuliah))
        populasi.append(individu)
    return populasi

# Evaluasi fitness
def evaluasi_fitness(individu):
    fitness = 0
    for i in range(len(individu)):
        for j in range(i + 1, len(individu)):
            if (individu[i][0] <= individu[j][0] < individu[i][1] or \
                individu[j][0] <= individu[i][0] < individu[j][1]) and \
                    individu[i][6] == individu[j][6]:
                fitness -= 1
    return fitness

# Seleksi orang tua menggunakan metode turnamen
def seleksi_orang_tua(populasi):
    ukuran_turnamen = 3
    orang_tua = []
    for _ in range(2):
        turnamen = random.sample(populasi, ukuran_turnamen)
        turnamen.sort(key=lambda x: evaluasi_fitness(x), reverse=True)
        orang_tua.append(turnamen[0])
    return orang_tua

# Rekombinasi dua orang tua menggunakan metode one-point crossover
def rekombinasi(orang_tua):
    anak = []
    titik_crossover = random.randint(1, len(jadwal_kuliah) - 1)
    anak.append(orang_tua[0][:titik_crossover] + orang_tua[1][titik_crossover:])
    anak.append(orang_tua[1][:titik_crossover] + orang_tua[0][titik_crossover:])
    return anak

# Mutasi anak menggunakan metode swap
def mutasi(anak):
    for i in range(len(anak)):
        if random.random() < 0.1:
            gen1 = random.randint(0, len(jadwal_kuliah) - 1)
            gen2 = random.randint(0, len(jadwal_kuliah) - 1)
            anak[i][gen1], anak[i][gen2] = anak[i][gen2], anak[i][gen1]
    return anak

# Algoritma genetika
def genetic_algorithm(jumlah_generasi, trace=False):
    populasi = inisialisasi_populasi(10)
    for generasi in range(jumlah_generasi):
        populasi_baru = []
        for _ in range(len(populasi) // 2):
            orang_tua = seleksi_orang_tua(populasi)
            anak = rekombinasi(orang_tua)
            anak_mutasi = mutasi(anak)
            populasi_baru.extend(anak_mutasi)
            if trace:
                print(f"Generasi {generasi+1} - Crossover: {orang_tua[0]} & {orang_tua[1]}")
                print(f"Generasi {generasi+1} - Mutasi: {anak_mutasi}")
        populasi = populasi_baru

    populasi.sort(key=lambda x: evaluasi_fitness(x), reverse=True)
    return populasi[0]

# Fungsi untuk menampilkan jadwal kuliah
def tampilkan_jadwal(jadwal):
    jadwal_df = pd.DataFrame(jadwal, columns=["Jam Mulai", "Jam Selesai", "Nama Dosen", "Mata Kuliah", "SKS", "Ruangan", "Hari"])
    return jadwal_df.to_string(index=False)

# Fungsi untuk menampilkan jadwal kuliah berdasarkan nama hari
def tampilkan_jadwal_per_hari(jadwal):
    jadwal_per_hari = {}
    for jadwal_item in jadwal:
        hari = jadwal_item[6]
        if hari not in jadwal_per_hari:
            jadwal_per_hari[hari] = []
        jadwal_per_hari[hari].append(jadwal_item)
    return jadwal_per_hari

# Fungsi untuk menampilkan jadwal kuliah ke dalam GUI
def tampilkan_jadwal_gui(jadwal):
    jadwal_per_hari = tampilkan_jadwal_per_hari(jadwal)
    jadwal_text.delete(1.0, END)
    for hari, jadwal_items in jadwal_per_hari.items():
        jadwal_df = pd.DataFrame(jadwal_items, columns=["Jam Mulai", "Jam Selesai", "Nama Dosen", "Mata Kuliah", "SKS", "Ruangan", "Hari"])
        jadwal_text.insert(END, jadwal_df.to_string(index=False))
        jadwal_text.insert(END, f"\nJumlah Hari: {hari}\n")
        jadwal_text.insert(END, "\n\n")

# Fungsi untuk menjalankan optimasi jadwal dengan metode Genetic Algorithm
def optimasi_jadwal():
    jumlah_generasi = int(generations_entry.get())
    jadwal_terbaik = genetic_algorithm(jumlah_generasi)
    tampilkan_jadwal_gui(jadwal_terbaik)

# Membuat GUI menggunakan Tkinter
root = Tk()
root.title("Optimasi Jadwal Kuliah")
root.geometry("600x600")

# Mengatur warna latar belakang dan tampilan tombol
root.configure(bg="#F0F0F0")
tambah_button_color = "#008080"
hapus_button_color = "#FF4500"
generate_button_color = "#006400"

# Mengatur warna latar belakang dan tampilan label dan kotak teks
label_bg_color = "#F0F0F0"
label_fg_color = "#000000"
text_bg_color = "#FFFFFF"
text_fg_color = "#000000"

input_frame = Frame(root)
input_frame.pack(pady=10)

jam_mulai_label = Label(input_frame, text="Jam Mulai:",border=True,borderwidth=1)
jam_mulai_label.grid(row=0, column=0)

jam_mulai_entry = Entry(input_frame)
jam_mulai_entry.grid(row=0, column=1)

jam_selesai_label = Label(input_frame, text="Jam Selesai:",border=True,borderwidth=1)
jam_selesai_label.grid(row=0, column=2)

jam_selesai_entry = Entry(input_frame)
jam_selesai_entry.grid(row=0, column=3)

nama_dosen_label = Label(input_frame, text="Nama Dosen:",border=True,borderwidth=1)
nama_dosen_label.grid(row=1, column=0)

nama_dosen_entry = Entry(input_frame)
nama_dosen_entry.grid(row=1, column=1)

mata_kuliah_label = Label(input_frame, text="Mata Kuliah:")
mata_kuliah_label.grid(row=1, column=2)

mata_kuliah_entry = Entry(input_frame)
mata_kuliah_entry.grid(row=1, column=3)

sks_label = Label(input_frame, text="Jumlah SKS:")
sks_label.grid(row=2, column=0)

sks_entry = Entry(input_frame)
sks_entry.grid(row=2, column=1)

ruangan_label = Label(input_frame, text="Ruangan:")
ruangan_label.grid(row=2, column=2)

ruangan_entry = Entry(input_frame)
ruangan_entry.grid(row=2, column=3)

hari_label = Label(input_frame, text="Hari:")
hari_label.grid(row=3, column=0)

hari_var = StringVar(root)
hari_var.set("Senin")  # Default hari: Senin

hari_option_menu = OptionMenu(input_frame, hari_var, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")
hari_option_menu.grid(row=3, column=1)

button_frame = Frame(root)
button_frame.pack(pady=5)

tambah_button = Button(button_frame, text="Tambah Jadwal", command=tambah_jadwal,bg=tambah_button_color, fg=text_fg_color)
tambah_button.pack(side=LEFT)

hapus_button = Button(button_frame, text="Hapus Jadwal Terakhir", command=hapus_jadwal,bg=hapus_button_color, fg=text_fg_color)
hapus_button.pack(side=LEFT)

jadwal_frame = Frame(root)
jadwal_frame.pack(pady=10)

jadwal_text = Text(jadwal_frame, height=20, width=100,border=True,borderwidth=1)
jadwal_text.pack()

generations_label = Label(root, text="Jumlah Generasi:")
generations_label.pack()

generations_entry = Entry(root)
generations_entry.pack()

optimasi_button = Button(root, text="Optimasi Jadwal", command=optimasi_jadwal)
optimasi_button.pack()

root.mainloop()
