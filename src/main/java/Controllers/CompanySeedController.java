package Controllers;

import Entities.Company;
import Services.CompanySeedService;

import java.util.List;
import java.util.Map;

public class CompanySeedController {

    private final CompanySeedService seedService;

    public CompanySeedController() {
        this.seedService = new CompanySeedService();
    }

    public List<Company> getCompaniesForCountry(String country) {
        return seedService.getCompaniesByCountry(country);
    }

    public List<Company> getRandomCompaniesForCountry(String country, int count) {
        return seedService.getRandomCompaniesByCountry(country, count);
    }

    public List<Company> searchCompanies(String query, String country) {
        return seedService.searchCompanies(query, country);
    }

    public List<String> getAvailableCountries() {
        return seedService.getAvailableCountries();
    }

    public Map<String, Integer> getCompanyCountByCountry() {
        return seedService.getCompanyCountByCountry();
    }

    public int getTotalCompanies() {
        return seedService.getTotalCompaniesCount();
    }
}