<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = ['success' => false, 'message' => 'Invalid request'];

    $data = [
        'program_code' => sanitizeInput($_POST['program_code']),
        'program_name' => sanitizeInput($_POST['program_name']),
        'description' => sanitizeInput($_POST['description'])
    ];

    switch ($action) {
        case 'add':
            $response = insertData('programs', $data)
                ? ['success' => true, 'message' => 'Program added successfully']
                : ['success' => false, 'message' => 'Error adding program'];
            break;

        case 'edit':
            $where = "program_id = " . intval($_POST['program_id']);
            $response = updateData('programs', $data, $where)
                ? ['success' => true, 'message' => 'Program updated successfully']
                : ['success' => false, 'message' => 'Error updating program'];
            break;

        case 'delete':
            $where = "program_id = " . intval($_POST['program_id']);
            $response = deleteData('programs', $where)
                ? ['success' => true, 'message' => 'Program deleted successfully']
                : ['success' => false, 'message' => 'Error deleting program'];
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
