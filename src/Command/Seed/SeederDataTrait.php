<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;

trait SeederDataTrait
{
    private const COMPANY_NAMES = [
        'Acme Corp', 'TechStart Inc', 'Global Solutions', 'Digital Dynamics', 'Innovate Labs',
        'Cloud Systems', 'Data Analytics Pro', 'Smart Solutions', 'Future Tech', 'NextGen Industries',
        'Enterprise Solutions', 'Business Partners', 'Creative Agency', 'Marketing Pro', 'Sales Force',
        'Service Masters', 'Consulting Group', 'Development Hub', 'Design Studio', 'Growth Partners'
    ];

    private const FIRST_NAMES = [
        'John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Jessica', 'William', 'Ashley',
        'James', 'Amanda', 'Christopher', 'Melissa', 'Daniel', 'Michelle', 'Matthew', 'Kimberly', 'Anthony', 'Amy'
    ];

    private const LAST_NAMES = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee'
    ];

    private const PROJECT_NAMES = [
        'Website Redesign', 'Mobile App Development', 'E-commerce Platform', 'API Integration',
        'Cloud Migration', 'Data Analytics Dashboard', 'CRM Implementation', 'Marketing Campaign',
        'Brand Identity', 'SEO Optimization', 'Content Management', 'Payment Gateway',
        'Inventory System', 'Customer Portal', 'Reporting Tool', 'Automation System'
    ];

    private const TASK_NAMES = [
        'Design Mockups', 'Frontend Development', 'Backend API', 'Database Schema',
        'Testing', 'Documentation', 'Code Review', 'Deployment', 'Bug Fixes',
        'Performance Optimization', 'Security Audit', 'User Training'
    ];

    private function getCompanyFromEntity(object $entity): ?Company
    {
        $reflection = new \ReflectionClass($entity);
        if (!$reflection->hasProperty('company')) {
            return null;
        }

        $property = $reflection->getProperty('company');
        $property->setAccessible(true);
        return $property->getValue($entity);
    }
}
