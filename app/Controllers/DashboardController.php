<?php

namespace App\Controllers;

use App\Services\LeadService;
use App\Services\EnrollmentService;

class DashboardController
{
    private LeadService $leadService;
    private EnrollmentService $enrollmentService;

    public function __construct(LeadService $leadService = null, EnrollmentService $enrollmentService = null)
    {
        $this->leadService = $leadService ?? new LeadService();
        $this->enrollmentService = $enrollmentService ?? new EnrollmentService();
    }

    public function index(): void
    {
        require_login();

        $totalLeads = $this->leadService->getTotalLeadsCount();
        $totalEnrollments = $this->enrollmentService->getTotalEnrollmentsCount();
        $revenue = $this->enrollmentService->getRevenueSum();

        render('dashboard/index', [
            'title' => 'Dashboard Tổng Quan',
            'totalLeads' => $totalLeads,
            'totalEnrollments' => $totalEnrollments,
            'revenue' => $revenue
        ]);
    }
}
