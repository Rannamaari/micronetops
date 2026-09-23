<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(string $service): View
    {
        $services = $this->services();

        abort_unless(isset($services[$service]), 404);

        return view('services.show', [
            'service' => $services[$service],
            'services' => $services,
        ]);
    }

    private function services(): array
    {
        return [
            'web-development' => [
                'name' => 'Websites & Web Development',
                'short' => 'Professional websites, portals, and online services made around your business.',
                'description' => 'A clear website is often the first place customers meet your business. We plan, design, and build sites that explain what you do and make it easy for customers to contact, book, or buy from you.',
                'deliverables' => ['Company websites and landing pages', 'Booking, enquiry, and customer portals', 'Online payments and integrations', 'Hosting, domains, and ongoing support'],
            ],
            'app-development' => [
                'name' => 'Custom Apps & Business Systems',
                'short' => 'Turn a workflow, spreadsheet, or business idea into a system your team can use.',
                'description' => 'Tell us where your work gets difficult or repetitive. We map the process and build practical web applications for operations, customers, reporting, approvals, and more.',
                'deliverables' => ['Custom dashboards and internal systems', 'Customer-facing web applications', 'Role-based access and approvals', 'Reports, notifications, and API integrations'],
            ],
            'pos-retail-systems' => [
                'name' => 'POS & Retail Systems',
                'short' => 'Retail tools for billing, inventory, sales visibility, and day-to-day control.',
                'description' => 'We create retail systems that make checkout and stock management simpler, while giving owners a clear view of sales and operations.',
                'deliverables' => ['Point of sale and billing workflows', 'Inventory and supplier tracking', 'Sales reports and operational dashboards', 'Staff roles and branch-ready setup'],
            ],
            'booking-services' => [
                'name' => 'Booking & Online Services',
                'short' => 'Give customers a simple way to discover, request, and book your service online.',
                'description' => 'From guest and activity bookings to service requests, we build straightforward customer journeys that help your business respond faster and stay organised.',
                'deliverables' => ['Booking and availability flows', 'Customer enquiry and confirmation journeys', 'Admin dashboards for teams', 'Website and messaging integrations'],
            ],
            'cctv-security' => [
                'name' => 'CCTV & Smart Security',
                'short' => 'Security camera planning, installation, configuration, and remote access.',
                'description' => 'We assess your space, recommend the right setup, and deliver CCTV systems that your team can use with confidence.',
                'deliverables' => ['Site assessment and coverage planning', 'CCTV installation and configuration', 'Remote viewing setup', 'Maintenance and support'],
            ],
            'networking-it' => [
                'name' => 'Networking & IT Infrastructure',
                'short' => 'Reliable connectivity and practical IT support for workplaces and properties.',
                'description' => 'We design and support networks that keep your team, devices, and systems connected without unnecessary complexity.',
                'deliverables' => ['Network design and Wi-Fi coverage', 'Router, switch, and cabling setup', 'Troubleshooting and upgrades', 'Ongoing IT support'],
            ],
            'ip-pbx-sip' => [
                'name' => 'IP PBX & SIP',
                'short' => 'Modern office calling with extensions, SIP connections, and smart call routing.',
                'description' => 'Make business communication easier with a properly configured IP PBX solution built around the way your team handles calls.',
                'deliverables' => ['IP PBX configuration', 'SIP connections and extensions', 'Call routing and business hours', 'Training and ongoing support'],
            ],
            'technology-consulting' => [
                'name' => 'Technology Consulting',
                'short' => 'Start with the right plan before investing in technology.',
                'description' => 'Whether you need a website, a custom system, security, or infrastructure, we help you define the requirement and choose a practical route forward.',
                'deliverables' => ['Requirement discovery sessions', 'System and workflow recommendations', 'Project scope and phased roadmaps', 'Vendor and technology guidance'],
            ],
        ];
    }
}
