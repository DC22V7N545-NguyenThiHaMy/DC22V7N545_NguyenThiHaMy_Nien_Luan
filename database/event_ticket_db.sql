create database if not exists event_ticket_db;
use event_ticket_db;

-- ==============================
-- 1. NGUOI DUNG
-- ==============================
create table nguoi_dung(
    ma_nguoi_dung int auto_increment primary key,
    ho_ten varchar(100) not null,
    email varchar(100) not null unique,
    mat_khau varchar(255) not null,
    so_dien_thoai varchar(20),
    dia_chi varchar(255),
    vai_tro enum('khach_hang', 'nhan_vien', 'quan_tri_vien')
        not null default 'khach_hang',
    ngay_tao timestamp default current_timestamp,
    ngay_cap_nhat timestamp default current_timestamp on update current_timestamp
);

-- ==============================
-- 2. DANH MUC SU KIEN
-- ==============================
create table danh_muc(
    ma_danh_muc int auto_increment primary key,
    ten_danh_muc varchar(100) not null unique,
    mo_ta text
);

-- ==============================
-- 3. SU KIEN
-- ==============================
create table su_kien(
    ma_su_kien int auto_increment primary key,
    ma_danh_muc int not null,
    ma_nguoi_tao int not null,
    ten_su_kien varchar(255) not null,
    mo_ta text,
    hinh_anh varchar(255),
    ngay_to_chuc date not null,
    gio_to_chuc time not null,
    dia_diem varchar(255) not null,
    trang_thai enum('ban_nhap','cho_duyet','da_duyet','da_huy')
        default 'cho_duyet',
    ngay_tao timestamp default current_timestamp,
    ngay_cap_nhat timestamp default current_timestamp on update current_timestamp,

    constraint fk_su_kien_danh_muc foreign key (ma_danh_muc) references danh_muc(ma_danh_muc),
    constraint fk_su_kien_nguoi_tao foreign key (ma_nguoi_tao) references nguoi_dung(ma_nguoi_dung)
);

-- ==============================
-- 4. LOAI VE
-- ==============================
create table loai_ve(
    ma_loai_ve int auto_increment primary key,
    ma_su_kien int not null,
    ten_loai_ve varchar(100) not null,
    gia_ve decimal(10,2) not null,
    so_luong int not null default 0,       -- tổng số vé phát hành
    so_luong_con int not null default 0,   -- số vé còn lại
    ngay_tao timestamp default current_timestamp,
    ngay_cap_nhat timestamp default current_timestamp on update current_timestamp,

    constraint fk_loai_ve_su_kien foreign key (ma_su_kien) references su_kien(ma_su_kien)
);

-- ==============================
-- 5. DON HANG
-- ==============================
create table don_hang(
    ma_don_hang int auto_increment primary key,
    ma_khach_hang int not null,
    ngay_dat timestamp default current_timestamp,
    tong_tien decimal(10,2) not null,
    phuong_thuc_thanh_toan varchar(100),

    trang_thai_thanh_toan enum('cho_thanh_toan','da_thanh_toan','that_bai','da_huy')
        default 'cho_thanh_toan',

    trang_thai_don_hang enum('cho_xac_nhan','da_xac_nhan','da_huy')
        default 'cho_xac_nhan',

    ngay_tao timestamp default current_timestamp,
    ngay_cap_nhat timestamp default current_timestamp on update current_timestamp,

    constraint fk_don_hang_khach_hang foreign key (ma_khach_hang) references nguoi_dung(ma_nguoi_dung)
);

-- ==============================
-- 6. CHI TIET DON HANG
-- ==============================
create table chi_tiet_don_hang(
    ma_chi_tiet int auto_increment primary key,
    ma_don_hang int not null,
    ma_loai_ve int not null,
    so_luong int not null,
    don_gia decimal(10,2) not null,
    thanh_tien decimal(10,2) not null,

    constraint fk_chi_tiet_don_hang foreign key (ma_don_hang) references don_hang(ma_don_hang),
    constraint fk_chi_tiet_loai_ve foreign key (ma_loai_ve) references loai_ve(ma_loai_ve)
);

-- ==============================
-- 7. VE DIEN TU (QR CODE)
-- ==============================
create table ve(
    ma_ve int auto_increment primary key,
    ma_chi_tiet int not null,
    ma_qr varchar(255) not null unique,    -- nội dung mã QR (duy nhất mỗi vé)
    qr_image_url varchar(500),             -- link ảnh QR
    trang_thai enum('chua_su_dung','da_su_dung','da_huy')
        default 'chua_su_dung',
    ngay_tao timestamp default current_timestamp,

    constraint fk_ve_chi_tiet foreign key (ma_chi_tiet) references chi_tiet_don_hang(ma_chi_tiet)
);

-- ==============================
-- 8. CHECK-IN (XAC NHAN VE)
-- ==============================
create table xac_nhan_ve(
    ma_xac_nhan int auto_increment primary key,
    ma_ve int not null,                    -- check-in theo từng vé cụ thể
    ma_nhan_vien int not null,
    thoi_gian_xac_nhan timestamp default current_timestamp,

    constraint fk_xac_nhan_ve foreign key (ma_ve) references ve(ma_ve),
    constraint fk_xac_nhan_nhan_vien foreign key (ma_nhan_vien) references nguoi_dung(ma_nguoi_dung)
);

-- ==============================
-- 9. THANH TOAN
-- ==============================
create table thanh_toan(
    ma_thanh_toan int auto_increment primary key,
    ma_don_hang int not null,
    phuong_thuc varchar(50),
    so_tien decimal(10,2),
    trang_thai enum('thanh_cong','that_bai','cho_xu_ly')
        default 'cho_xu_ly',
    thoi_gian timestamp default current_timestamp,

    constraint fk_thanh_toan_don_hang foreign key (ma_don_hang) references don_hang(ma_don_hang)
);

-- ==============================
-- 10. TIN TUC
-- ==============================
create table tin_tuc(
    ma_tin_tuc int auto_increment primary key,
    ma_nguoi_tao int not null,
    tieu_de varchar(255) not null,
    noi_dung longtext,
    hinh_anh varchar(255),
    trang_thai enum('nhap','da_duyet','da_xoa') default 'nhap',
    ngay_tao timestamp default current_timestamp,
    ngay_cap_nhat timestamp default current_timestamp on update current_timestamp,

    constraint fk_tin_tuc_nguoi_tao foreign key (ma_nguoi_tao) references nguoi_dung(ma_nguoi_dung)
);

-- ==============================
-- DU LIEU MAC DINH
-- ==============================
-- Tài khoản Admin mặc định: email = admin@gmail.com | mật khẩu = Admin@123
INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, vai_tro) VALUES
('Admin', 'admin@gmail.com', '$2y$10$L.j1FxvkDspr8sTzfStNuesPD9a6f3/JF8zmewbSQvZxG4z1bObCS', '0900000000', 'Admin Office', 'quan_tri_vien');
