<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController {

    public function index() {
        $cliente = new Cliente();
        $clientes = $cliente->getAll();
        require __DIR__ . '/../views/clientes/index.php';
    }

    public function create() {
        require __DIR__ . '/../views/clientes/create.php';
    }

    public function store() {
        $cliente = new Cliente();
        $cliente->create($_POST);
        header("Location: index.php?page=clientes");
        exit;
    }

    public function edit() {
        $id = $_GET['id'];
        $cliente = new Cliente();
        $c = $cliente->getById($id);
        require __DIR__ . '/../views/clientes/edit.php';
    }

    public function update() {
        $id = $_POST['id'];
        $cliente = new Cliente();
        $cliente->update($id, $_POST);
        header("Location: index.php?page=clientes");
        exit;
    }

    public function delete($id) {
        $cliente = new Cliente();
        $cliente->delete($id);
        header("Location: index.php?page=clientes");
        exit;
    }
}