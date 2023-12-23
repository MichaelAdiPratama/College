import tkinter as tk
import random

mata_kuliah = [' Metnum ', ' AOK ', ' Applied Statistics ', ' Data Mining ', ' IMK ', ' Kecerdasan Buatan ', ' Sistem Operasi ', ' Teknologi Web ', ' Basis Data ', ' Struktur Data ', ' TKTI ', ' ADSI ', ' Jarkom ']
dosen = [' Leo Willyanto ', ' Stephanus Ananda ', ' Siana Halim ', ' Gregorius Satiabudhi ', ' Andreas Handojo ', ' Gregorius Satiabudhi ', ' Rudy Adipranata ', ' Krisna Wahyudi ', ' Adi Wibowo ', ' liliana ', ' Alex Kurniawan ' , ' silvia rostyaningsih ' , ' Justinus Andjarwirawan ' ]
ruangan = [' P 203 ',  ' P 503 ', ' Puskom P ', ' P 708 ', ' P 203 ', ' P 707 ', ' P 203 ', ' Lab SI ', ' Lab SI ', ' P 707 ', ' P 504 ', ' Lab PA ', ' I 202 ']


jumlah_hari = 5
jam_kerja_per_hari = 5


jumlah_populasi = 10
jumlah_generasi = 100


panjang_kromosom = jumlah_hari * jam_kerja_per_hari


def fitness(individu):

    konflik = 0
    for i in range(panjang_kromosom):
        for j in range(i + 1, panjang_kromosom):
            if individu[i] == individu[j]:
                konflik += 1
    return konflik


def crossover(individu1, individu2):
    titik_crossover = random.randint(1, panjang_kromosom - 1)
    anak1 = individu1[:titik_crossover] + individu2[titik_crossover:]
    anak2 = individu2[:titik_crossover] + individu1[titik_crossover:]
    return anak1, anak2


def mutasi(individu):
    titik_mutasi = random.randint(0, panjang_kromosom - 1)
    nilai_mutasi = random.randint(1, len(mata_kuliah))
    individu[titik_mutasi] = nilai_mutasi
    return individu


def inisialisasi_populasi():
    populasi = []
    for _ in range(jumlah_populasi):
        individu = [random.randint(1, len(mata_kuliah)) for _ in range(panjang_kromosom)]
        populasi.append(individu)
    return populasi


def seleksi_turnamen(populasi):
    ukuran_turnamen = 3
    turnamen = random.sample(populasi, ukuran_turnamen)
    turnamen.sort(key=lambda x: fitness(x))
    return turnamen[0]

def genetic_algorithm():
    populasi = inisialisasi_populasi()
    for generasi in range(jumlah_generasi):
        populasi = sorted(populasi, key=lambda x: fitness(x))
        orang_tua1 = seleksi_turnamen(populasi)
        orang_tua2 = seleksi_turnamen(populasi)
        anak1, anak2 = crossover(orang_tua1, orang_tua2)
        anak1 = mutasi(anak1)
        anak2 = mutasi(anak2)
        populasi[-2] = anak1
        populasi[-1] = anak2
    populasi = sorted(populasi, key=lambda x: fitness(x))
    return populasi[0]


def jadwal_ke_tabel(jadwal):
    tabel = [["" for _ in range(jam_kerja_per_hari + 1)] for _ in range(jumlah_hari + 1)]
    tabel[0][0] = "Hari/Jam"
    for jam in range(jam_kerja_per_hari):
    
        if (jam + 1) == 1:
            tabel[0][jam + 1] = "08.30-10.30"
        if (jam + 1) == 2:
            tabel[0][jam + 1] = "10.30-12.30"
        if (jam + 1) == 3:
            tabel[0][jam + 1] = "12.30-14.30"
        if (jam + 1) == 4:
            tabel[0][jam + 1] = "14.30-16.30"
        if (jam + 1) == 5:
            tabel[0][jam + 1] = "16.30-18.30"

    for hari in range(jumlah_hari):
        if (hari+1) == 1:
         
            tabel[hari + 1][0] = "Senin"
        if (hari+1) == 2:
   
            tabel[hari + 1][0] = "Selasa"
        if (hari+1) == 3:
          
            tabel[hari + 1][0] = "Rabu"
        if (hari+1) == 4:
           
            tabel[hari + 1][0] = "Kamis"
        if (hari+1) == 5:
            tabel[hari + 1][0] = "Jum'at"
            
        if (hari+1) == 6:
            tabel[hari + 1][0] = "Sabtu"
        
        for jam in range(jam_kerja_per_hari):
            indeks = hari * jam_kerja_per_hari + jam
            mata_kuliah_idx = jadwal[indeks] - 1
            jadwal_str = mata_kuliah[mata_kuliah_idx] + dosen[mata_kuliah_idx] + ruangan[mata_kuliah_idx]
            tabel[hari + 1][jam + 1] = jadwal_str
    return tabel

def perbarui_jadwal():
    jadwal_terbaik = genetic_algorithm()
    tabel_jadwal = jadwal_ke_tabel(jadwal_terbaik)
    for i in range(jumlah_hari + 1):
        for j in range(jam_kerja_per_hari + 1):
            jadwal_entry = tk.Entry(frame_jadwal, width=40)
            jadwal_entry.insert(0, tabel_jadwal[i][j])
            jadwal_entry.grid(row=i, column=j)


window = tk.Tk()
window.title("Optimasi Jadwal Kuliah")

frame_jadwal = tk.Frame(window)
frame_jadwal.pack()

tombol_perbarui = tk.Button(window, text="Perbarui Jadwal", command=perbarui_jadwal)
tombol_perbarui.pack()

perbarui_jadwal()

window.mainloop()
