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

    public List<Company> getMergedCompaniesForCountry(String country) {
        return seedService.getMergedCompaniesForCountry(country);
    }

    public List<Company> getMergedRandomCompaniesForCountry(String country, int count) {
        return seedService.getMergedRandomCompaniesForCountry(country, count);
    }

    public List<Company> searchMergedCompanies(String query, String country) {
        return seedService.searchMergedCompanies(query, country);
    }

    public List<Company> getMergedCompaniesForMarketCountry(String countryCode, String countryName) {
        return seedService.getMergedCompaniesForMarket(countryCode, countryName, null);
    }

    public List<Company> getMergedCompaniesForMarketCountry(String countryCode, String countryName, String region) {
        return seedService.getMergedCompaniesForMarket(countryCode, countryName, region);
    }

    public List<Company> getMergedCompaniesForMarket(String countryCode, String countryName, String region) {
        return seedService.getMergedCompaniesForMarket(countryCode, countryName, region);
    }

    public List<String> getAvailableCountries() {
        return seedService.getAvailableCountries();
    }

    public List<String> getMergedAvailableCountries() {
        return seedService.getMergedAvailableCountries();
    }

    public Map<String, Integer> getCompanyCountByCountry() {
        return seedService.getCompanyCountByCountry();
    }

    public Map<String, Integer> getMergedCompanyCountByCountry() {
        return seedService.getMergedCompanyCountByCountry();
    }

    public int getTotalCompanies() {
        return seedService.getTotalCompaniesCount();
    }

    public int getMergedTotalCompanies() {
        return seedService.getMergedTotalCompaniesCount();
    }
}