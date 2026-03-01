package Services;

import DAO.CompanyDAO;
import DAO.ManagerDAO;
import Entities.Company;
import Entities.Manager;

import java.sql.SQLException;
import java.util.List;

public class CompanyService {
    private final CompanyDAO companyDAO;
    private final ManagerDAO managerDAO;

    public CompanyService() {
        this.companyDAO = new CompanyDAO();
        this.managerDAO = new ManagerDAO();
    }

    public Company createCompany(String companyName, String taxNumber,
                                 String registrationNumber, String address,
                                 String contactEmail, String contactPhone,
                                 Long managerId) throws SQLException {

        if (companyName == null || companyName.trim().isEmpty()) {
            throw new IllegalArgumentException("Company name is required");
        }

        if (taxNumber != null && !taxNumber.trim().isEmpty()) {
            if (companyDAO.taxNumberExists(taxNumber)) {
                throw new IllegalArgumentException("Matricule fiscale already registered");
            }
        }

        if (managerId != null) {
            Manager manager = managerDAO.findById(managerId);
            if (manager == null) {
                throw new IllegalArgumentException("Manager not found");
            }
        }

        Company company = new Company();
        company.setCompanyName(companyName);
        company.setTaxNumber(taxNumber);
        company.setRegistrationNumber(registrationNumber);
        company.setCountry("Tunisia");
        company.setAddress(address);
        company.setContactEmail(contactEmail);
        company.setContactPhone(contactPhone);
        company.setRating(0);
        company.setWarnings(0);
        company.setBanned(false);
        company.setCompanyManagerId(managerId);

        Company created = companyDAO.create(company);

        if (managerId != null) {
            Manager manager = managerDAO.findById(managerId);
            manager.setCompanyId(created.getId());
            managerDAO.update(manager);
        }

        return created;
    }

    public Company getCompanyById(Long id) throws SQLException {
        return companyDAO.findById(id);
    }

    public Company getCompanyByManagerId(Long managerId) throws SQLException {
        return companyDAO.findByManagerId(managerId);
    }

    public Company getCompanyByTaxNumber(String taxNumber) throws SQLException {
        return companyDAO.findByTaxNumber(taxNumber);
    }

    public List<Company> getAllCompanies() throws SQLException {
        return companyDAO.findAll();
    }

    public List<Company> getActiveCompanies() throws SQLException {
        return companyDAO.findActiveCompanies();
    }

    public List<Company> getCompaniesByCountry(String country) throws SQLException {
        return companyDAO.findByCountry(country);
    }

    public List<Company> searchCompanies(String searchQuery) throws SQLException {
        if (searchQuery == null || searchQuery.trim().isEmpty()) {
            return getAllCompanies();
        }
        return companyDAO.searchByName(searchQuery);
    }
    
    public boolean updateCompany(Company company) throws SQLException {
        if (company.getId() == null) {
            throw new IllegalArgumentException("Company ID is required for update");
        }

        if (company.getCompanyName() == null || company.getCompanyName().trim().isEmpty()) {
            throw new IllegalArgumentException("Company name is required");
        }
        
        Company existing = companyDAO.findById(company.getId());
        if (existing != null && company.getTaxNumber() != null) {
            if (!company.getTaxNumber().equals(existing.getTaxNumber())) {
                if (companyDAO.taxNumberExists(company.getTaxNumber())) {
                    throw new IllegalArgumentException("Matricule fiscale already in use");
                }
            }
        }

        return companyDAO.update(company);
    }

    public boolean deleteCompany(Long companyId) throws SQLException {
        return companyDAO.delete(companyId);
    }

    public boolean setBanStatus(Long companyId, boolean banned) throws SQLException {
        Company company = companyDAO.findById(companyId);
        if (company == null) {
            throw new IllegalArgumentException("Company not found");
        }

        company.setBanned(banned);
        return companyDAO.update(company);
    }

    public boolean addWarning(Long companyId) throws SQLException {
        Company company = companyDAO.findById(companyId);
        if (company == null) {
            throw new IllegalArgumentException("Company not found");
        }

        company.setWarnings(company.getWarnings() + 1);
        return companyDAO.update(company);
    }

    public Company findByNameAndCountry(String name, String country) throws SQLException {
        List<Company> allCompanies = companyDAO.findAll();

        return allCompanies.stream()
                .filter(c -> c.getCompanyName().equalsIgnoreCase(name) &&
                        c.getCountry().equalsIgnoreCase(country))
                .findFirst()
                .orElse(null);
    }

    public boolean updateRating(Long companyId, int rating) throws SQLException {
        if (rating < 0 || rating > 5) {
            throw new IllegalArgumentException("Rating must be between 0 and 5");
        }

        Company company = companyDAO.findById(companyId);
        if (company == null) {
            throw new IllegalArgumentException("Company not found");
        }

        company.setRating(rating);
        return companyDAO.update(company);
    }

    public long countCompanies() throws SQLException {
        return companyDAO.count();
    }

    public boolean companyExists(Long companyId) throws SQLException {
        return companyDAO.exists(companyId);
    }
}