<?php
class AdminController extends Controller
{
    public function dashboard()
    {
        $this->view("admin/dashboard", [
            "title" => "Кабинет администратора",
            "stats" => (new Appointment())->dashboardStats(),
            "recent" => (new Appointment())->adminList(["status" => "pending"]),
            "unreadCount" => (new Notification())->unreadCount(),
            "noindex" => true
        ]);
    }
}
