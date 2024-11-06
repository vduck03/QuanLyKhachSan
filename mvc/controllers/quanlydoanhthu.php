<?php
class quanlydoanhthu extends Controller
{
    public function start()
    {

        $HoaDonModel = $this->model("HoaDonModel");

        $dataHoadon = $HoaDonModel->getAllHoaDon();

        if (isset($_POST['locdoanhthu'])) {
            $loc = $_POST['lochoadon'];
            $dataLoc = [];

            if ($loc == "theongay") {
                $currTime = date('d');
                $dataLoc = $HoaDonModel->getTheoNgay($currTime);
            } elseif ($loc == "theothang") {
                $currTime = date('m');
                $dataLoc = $HoaDonModel->getTheoThang($currTime);
            } elseif ($loc == "theonam") {
                $currTime = date('Y');
                $dataLoc = $HoaDonModel->getTheoNam($currTime);
            }
        }

        $this->view("defaultLayout", [
            "container" => "quanlydoanhthu",
            "dataHoadon" => $dataHoadon,
            "dataLoc" => $dataLoc
        ]);
    }

    public function chitiet($sohopdong)
    {
        $ServiceModel = $this->model("ServiceModel");
        $RoomOrdered = $this->model("RoomOrdered");

        $datadichvu = $ServiceModel->layDichVuDaDangKyQuaSHD($sohopdong);
        $dataPhongDaDat = $RoomOrdered->layPhongDaDatQuaSHD($sohopdong);


        $this->view("defaultLayout", [
            "container" => "chitiethopdongkhachhang",
            "datadichvu" => $datadichvu,
            "dataPhongDaDat" => $dataPhongDaDat
        ]);
    }
}
