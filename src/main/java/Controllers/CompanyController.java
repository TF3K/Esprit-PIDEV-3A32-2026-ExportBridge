package Controllers;

import Entities.Company;
import Services.CompanyService;

import java.sql.SQLException;
import java.util.List;

public class CompanyController {
    private final CompanyService companyService;

    public CompanyController() {
        this.companyService = new CompanyService();
    }

    public Company createCompany(String companyName, String taxNumber,
                                 String registrationNumber, String address,
                                 String contactEmail, String contactPhone,
                                 Long managerId) {
        try {
            return companyService.createCompany(companyName, taxNumber,
                    registrationNumber, address,
                    contactEmail, contactPhone, managerId);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    public Company getCompany(Long id) {
        try {
            return companyService.getCompanyById(id);
        } catch (SQLException e) {
            System.err.println("Error fetching company: " + e.getMessage());
            return null;
        }
    }

    public Company getCompanyByManager(Long managerId) {
        try {
            return companyService.getCompanyByManagerId(managerId);
        } catch (SQLException e) {
            System.err.println("Error fetching company: " + e.getMessage());
            return null;
        }
    }

    public List<Company> getAllCompanies() {
        try {
            return companyService.getAllCompanies();
        } catch (SQLException e) {
            System.err.println("Error fetching companies: " + e.getMessage());
            return List.of();
        }
    }

    public List<Company> getActiveCompanies() {
        try {
            return companyService.getActiveCompanies();
        } catch (SQLException e) {
            System.err.println("Error fetching companies: " + e.getMessage());
            return List.of();
        }
    }

    public List<Company> searchCompanies(String query) {
        try {
            return companyService.searchCompanies(query);
        } catch (SQLException e) {
            System.err.println("Error searching companies: " + e.getMessage());
            return List.of();
        }
    }

    public boolean updateCompany(Company company) {
        try {
            return companyService.updateCompany(company);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean deleteCompany(Long companyId) {
        try {
            return companyService.deleteCompany(companyId);
        } catch (SQLException e) {
            System.err.println("Error deleting company: " + e.getMessage());
            return false;
        }
    }

    public boolean setBanStatus(Long companyId, boolean banned) {
        try {
            return companyService.setBanStatus(companyId, banned);
        } catch (SQLException e) {
            System.err.println("Error updating ban status: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean updateRating(Long companyId, int rating) {
        try {
            return companyService.updateRating(companyId, rating);
        } catch (SQLException e) {
            System.err.println("Error updating rating: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }
}