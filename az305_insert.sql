-- Batch Insert Script for AZ-305 Certification
-- Usage: Execute this script in your MySQL client.

START TRANSACTION;

-- ========================================================
-- Certification: AZ-305
-- ========================================================
INSERT INTO certifications (name) VALUES ('AZ-305: Designing Microsoft Azure Infrastructure Solutions');
SET @cert_id = LAST_INSERT_ID();

-- ========================================================
-- Domain 1: Design identity, governance, and monitoring solutions
-- ========================================================
INSERT INTO domains (certification_id, name) VALUES (@cert_id, 'Design identity, governance, and monitoring solutions (25–30%)');
SET @dom_id = LAST_INSERT_ID();
INSERT INTO domain_links (domain_id, url) VALUES (@dom_id, 'https://learn.microsoft.com/en-us/training/paths/design-identity-governance-monitor-solutions/');

    -- Subdomain 1.1
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design solutions for logging and monitoring');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-solution-to-log-monitor-azure-resources/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a logging solution'),
    (@sub_id, 'Recommend a solution for routing logs'),
    (@sub_id, 'Recommend a monitoring solution');

    -- Subdomain 1.2
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design authentication and authorization solutions');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-authentication-authorization-solutions/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend an authentication solution'),
    (@sub_id, 'Recommend an identity management solution'),
    (@sub_id, 'Recommend a solution for authorizing access to Azure resources'),
    (@sub_id, 'Recommend a solution for authorizing access to on-premises resources'),
    (@sub_id, 'Recommend a solution to manage secrets, certificates, and keys');

    -- Subdomain 1.3
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design governance');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-governance/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a structure for management groups, subscriptions, and resource groups, and a strategy for resource tagging'),
    (@sub_id, 'Recommend a solution for managing compliance'),
    (@sub_id, 'Recommend a solution for identity governance');

-- ========================================================
-- Domain 2: Design data storage solutions
-- ========================================================
INSERT INTO domains (certification_id, name) VALUES (@cert_id, 'Design data storage solutions (20–25%)');
SET @dom_id = LAST_INSERT_ID();
INSERT INTO domain_links (domain_id, url) VALUES (@dom_id, 'https://learn.microsoft.com/en-us/training/paths/design-data-storage-solutions/');

    -- Subdomain 2.1
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design data storage solutions for relational data');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-data-storage-solution-for-relational-data/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a solution for storing relational data'),
    (@sub_id, 'Recommend a database service tier and compute tier'),
    (@sub_id, 'Recommend a solution for database scalability'),
    (@sub_id, 'Recommend a solution for data protection');

    -- Subdomain 2.2
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design data storage solutions for semi-structured and unstructured data');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-data-storage-solution-for-non-relational-data/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a solution for storing semi-structured data'),
    (@sub_id, 'Recommend a solution for storing unstructured data'),
    (@sub_id, 'Recommend a data storage solution to balance features, performance, and costs'),
    (@sub_id, 'Recommend a data solution for protection and durability');

    -- Subdomain 2.3
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design data integration');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-data-integration/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a solution for data integration'),
    (@sub_id, 'Recommend a solution for data analysis');

-- ========================================================
-- Domain 3: Design business continuity solutions
-- ========================================================
INSERT INTO domains (certification_id, name) VALUES (@cert_id, 'Design business continuity solutions (15–20%)');
SET @dom_id = LAST_INSERT_ID();
INSERT INTO domain_links (domain_id, url) VALUES (@dom_id, 'https://learn.microsoft.com/en-us/training/paths/design-business-continuity-solutions/');

    -- Subdomain 3.1
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design solutions for backup and disaster recovery');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-solution-for-backup-disaster-recovery/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a recovery solution for Azure and hybrid workloads that meets recovery objectives'),
    (@sub_id, 'Recommend a backup and recovery solution for compute'),
    (@sub_id, 'Recommend a backup and recovery solution for databases'),
    (@sub_id, 'Recommend a backup and recovery solution for unstructured data');

    -- Subdomain 3.2
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design for high availability');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/describe-high-availability-disaster-recovery-strategies/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a high availability solution for compute'),
    (@sub_id, 'Recommend a high availability solution for relational data'),
    (@sub_id, 'Recommend a high availability solution for semi-structured and unstructured data');

-- ========================================================
-- Domain 4: Design infrastructure solutions
-- ========================================================
INSERT INTO domains (certification_id, name) VALUES (@cert_id, 'Design infrastructure solutions (30–35%)');
SET @dom_id = LAST_INSERT_ID();
INSERT INTO domain_links (domain_id, url) VALUES (@dom_id, 'https://learn.microsoft.com/en-us/training/paths/design-infranstructure-solutions/');

    -- Subdomain 4.1
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design compute solutions');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-compute-solution/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a virtual machine-based solution'),
    (@sub_id, 'Recommend a container-based solution'),
    (@sub_id, 'Recommend a serverless-based solution'),
    (@sub_id, 'Recommend a compute solution for batch processing');

    -- Subdomain 4.2
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design an application architecture');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-application-architecture/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a messaging architecture'),
    (@sub_id, 'Recommend an event-driven architecture'),
    (@sub_id, 'Recommend a solution for API integration'),
    (@sub_id, 'Recommend a caching solution for applications'),
    (@sub_id, 'Recommend an application configuration management solution'),
    (@sub_id, 'Recommend an automated deployment solution for applications');

    -- Subdomain 4.3
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design migrations');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-application-architecture/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Evaluate a migration solution that leverages the Microsoft Cloud Adoption Framework for Azure'),
    (@sub_id, 'Evaluate on-premises servers, data, and applications for migration'),
    (@sub_id, 'Recommend a solution for migrating workloads to infrastructure as a service (IaaS) and platform as a service (PaaS)'),
    (@sub_id, 'Recommend a solution for migrating databases'),
    (@sub_id, 'Recommend a solution for migrating unstructured data');

    -- Subdomain 4.4
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Design network solutions');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/modules/design-network-solutions/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Recommend a connectivity solution that connects Azure resources to the internet'),
    (@sub_id, 'Recommend a connectivity solution that connects Azure resources to on-premises networks'),
    (@sub_id, 'Recommend a solution to optimize network performance'),
    (@sub_id, 'Recommend a solution to optimize network security'),
    (@sub_id, 'Recommend a load-balancing and routing solution');

-- ========================================================
-- Domain 5: General Skills
-- ========================================================
INSERT INTO domains (certification_id, name) VALUES (@cert_id, 'General Skills');
SET @dom_id = LAST_INSERT_ID();

    -- Subdomain 5.1
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Well-Architected Framework');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/paths/azure-well-architected-framework/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Well-Architected Framework');

    -- Subdomain 5.2
    INSERT INTO subdomains (domain_id, name) VALUES (@dom_id, 'Cloud Adoption Framework');
    SET @sub_id = LAST_INSERT_ID();
    INSERT INTO subdomain_links (subdomain_id, url) VALUES (@sub_id, 'https://learn.microsoft.com/en-us/training/paths/cloud-adoption-framework/');
    
    INSERT INTO subjects (subdomain_id, name) VALUES 
    (@sub_id, 'Cloud Adoption Framework');

COMMIT;