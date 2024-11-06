<?php
class ChiTietPhongForNhanVien extends Controller
{
    public function start($roomID)
    {
        $roomModel = $this->model("RoomModel");
        $customerModel = $this->model("CustomerModel");
        $roomOrdered = $this->model("RoomOrdered");
        $serviceModel = $this->model("ServiceModel");
        $hoaDonModel = $this->model("HoaDonModel");

        ///xu ly dat phong
        if (isset($_POST['datphong'])) {

            // lay du lieu tu form dat phong
            $tenkhachhang = $_POST['tenkhachhang'];
            $cccd = $_POST['cccd'];
            $sdt = $_POST['sdt'];
            $ngaysinh = $_POST['ngaysinh'];
            $quoctich = $_POST['quoctich'];
            $diachi = $_POST['diachi'];
            $gioitinh = $_POST['gioitinh'];
            $ngaydat = date('Y-m-d H:i:s');
            $phuongthucthanhtoan = $_POST['phuongthucthanhtoan'];
            $tiencoc = $_POST['tiencoc'];
            $loaiphong =  $roomModel->getLoaiPhong($roomID);
            $gia =  $roomModel->getGiaPhong($roomID);


            //kiem tra xem có tồn tại kh
            $checkCustomer = $customerModel->getACustomer($tenkhachhang);
            $dataCheckCustomer = mysqli_num_rows($checkCustomer);

            $dataC = mysqli_fetch_row($checkCustomer);

            if ($dataCheckCustomer > 0 && $dataC[9] == "Đã đặt") {
                echo "err";
            } else if ($dataCheckCustomer > 0 && $dataC[9] == "Đã trả") {
                $roomOrdered->addRoomOrdered($roomID, $ngaydat, '', $loaiphong, $gia, $tenkhachhang, $cccd, $sdt, $ngaysinh, $quoctich, $diachi, $gioitinh);
                $roomModel->updateStatusRoom($roomID, 'Đã đặt', $tenkhachhang);
                $customerModel->suatrangdat($tenkhachhang);

                $dataR = $roomOrdered->getSHD($roomID, $tenkhachhang, $ngaydat);
                $shd = mysqli_fetch_row($dataR);

                $hoaDonModel->addHoaDon($tenkhachhang, $roomID, $ngaydat, '', '', 0, $shd[5], $tiencoc, $phuongthucthanhtoan);
            } else {
                $roomOrdered->addRoomOrdered($roomID, $ngaydat, '', $loaiphong, $gia, $tenkhachhang, $cccd, $sdt, $ngaysinh, $quoctich, $diachi, $gioitinh);
                $roomModel->updateStatusRoom($roomID, 'Đã đặt', $tenkhachhang);
                $customerModel->addCustomer($tenkhachhang, $cccd, $sdt, $ngaysinh, $quoctich, $diachi, $gioitinh, 1, 'Đã đặt');

                $dataR = $roomOrdered->getSHD($roomID, $tenkhachhang, $ngaydat);
                $shd = mysqli_fetch_row($dataR);

                $hoaDonModel->addHoaDon($tenkhachhang, $roomID, $ngaydat, '', '', 0, $shd[5], $tiencoc, $phuongthucthanhtoan);
            }
        }

        if (isset($_POST['btnnhanphong'])) {
            $ngaynhanphong = $_POST['ngaynhanphong'];

            $dataRoom = $roomModel->getARoom($roomID);
            $room = mysqli_fetch_row($dataRoom);


            $hoaDonModel->updateNgayNhan($room[3], $roomID, $ngaynhanphong);
            $roomModel->updateTrangthaiphong($roomID, 'Đã nhận');
        }

        if (isset($_POST['btntraphong'])) {
            $ngaytraphong = $_POST['ngaytraphong'];

            $dataRoom = $roomModel->getARoom($roomID);
            $room = mysqli_fetch_row($dataRoom);


            $hoaDonModel->updateNgayTra($room[3], $roomID, $ngaytraphong);
            $roomModel->updateTrangthaiphong($roomID, 'Đang trống');
            $roomModel->setPhongtrong($roomID);
            $customerModel->suatrangthaidat($room[3]);

            $dataRoomAlone = $roomModel->getRoomType("Phòng đơn");
            $dataRoomCouple = $roomModel->getRoomType("Phòng đôi");
            $dataRoomFamily = $roomModel->getRoomType("Phòng gia đình");

            $this->view("defaultLayout", [
                "container" => "QuanLyPhongForNhanVien",
                "roomAlone" => $dataRoomAlone,
                "roomCouple" => $dataRoomCouple,
                "roomFamily" => $dataRoomFamily,
                "hoadonModel" => $hoaDonModel
            ]);
        }

        if (isset($_POST['datdichvu'])) {
            $arrTendichvu = [];
            $arrSoluong = [];
            $arrGia = [];
            $arrThanhtien = [];
            $lengthService = $_POST['lengthService'];

            $tenkhachhang = $_POST['kh'];
            $ngaydat = $_POST['ngayd'];
            $dataR = $roomOrdered->getSHD($roomID, $tenkhachhang, $ngaydat);
            $sohopdong = mysqli_fetch_assoc($dataR);
            for ($i = 0; $i < $lengthService; $i++) {
                array_push($arrTendichvu, $_POST['tendichvu' . $i + 1]);
                array_push($arrSoluong, $_POST['thanhtien' . $i + 1] / $_POST['gia' . $i + 1]);
                array_push($arrGia, $_POST['gia' . $i + 1]);
                array_push($arrThanhtien, $_POST['thanhtien' . $i + 1]);
            }

            for ($j = 0; $j < count($arrSoluong); $j++) {
                if ($arrSoluong[$j] > 0) {
                    $serviceModel->addService($tenkhachhang, $sohopdong['sohopdong'], $arrTendichvu[$j], $arrSoluong[$j], $arrGia[$j], $arrThanhtien[$j]);
                }
            }
            echo '<script>alert("Bạn đã đặt xong các dịch vụ");</script>';
            echo '<script>window.history.back();</script>';
        }

        $dataService = $serviceModel->getAllService();
        $dataRoom =  $roomModel->getARoom($roomID);
        $room = mysqli_fetch_row($dataRoom);

        if (isset($_GET['roomID'])) {
            var_dump($_GET['roomID']);
        }
        $this->view("defaultLayout", [
            "container" => "ChiTietPhongForNhanVien",
            "room" => $room,
            "dataService" => $dataService,
            "hoaDonModel" => $hoaDonModel
        ]);
    }


    public function sua($id)
    {

        $roomModel = $this->model("RoomModelForNhanVien");
        $hoaDonModel = $this->model("HoaDonModel");
        $dataRoom = $roomModel->getARoom($id);

        if (isset($_POST['btnsuaphong'])) {
            $room = mysqli_fetch_row($dataRoom);

            if ($room[2] == "Đang trống") {
                $tenphong = $_POST['tenphong'];
                $loaiphong = $_POST['loaiphong'];
                $trangthaiphong = $_POST['trangthaiphong'];
                $gia = $_POST['gia'];

                $roomModel->updateARoom($tenphong,  $loaiphong, $trangthaiphong, '', $gia);
            } else if ($room[2] == "Đã đặt") {
                $tenphong = $_POST['tenphong'];
                $loaiphong = $_POST['loaiphong'];
                $trangthaiphong = $_POST['trangthaiphong'];
                $ngaydat = $_POST['ngaydat'];
                $khachhang = $_POST['khachhang'];
                $gia = $_POST['gia'];

                $hoaDonModel->updateHoaDon($khachhang, $tenphong, $ngaydat, '');
                $roomModel->updateARoom($tenphong, $loaiphong, $trangthaiphong, $khachhang, $gia);
            } else {
                $tenphong = $_POST['tenphong'];
                $loaiphong = $_POST['loaiphong'];
                $trangthaiphong = $_POST['trangthaiphong'];
                $ngaydat = $_POST['ngaydat'];
                $ngaynhan = $_POST['ngaynhan'];
                $khachhang = $_POST['khachhang'];
                $gia = $_POST['gia'];

                $hoaDonModel->updateHoaDon($khachhang, $tenphong, $ngaydat, $ngaynhan);
                $roomModel->updateARoom($tenphong, $loaiphong, $trangthaiphong, $ngaydat, $ngaynhan, $khachhang, $gia);
            }
        }


        $dataRoom = $roomModel->getARoom($id);



        $this->view("fullLayout", [
            "container" => "SuaPhongForNhanVien",
            "dataRoom" => $dataRoom,
            "hoaDonModel" => $hoaDonModel
        ]);
    }
}
