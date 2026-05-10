package Controllers;

import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;
import Services.MarketService;

import java.sql.SQLException;
import java.util.List;
import java.util.Map;

public class MarketController {
    private final MarketService marketService;

    public MarketController() {
        this.marketService = new MarketService();
    }

    public Market createMarket(String countryCode, String name, String region,
            boolean isEU, String description, String tradeAgreement) {
        try {
            return marketService.createMarket(countryCode, name, region,
                    isEU, description, tradeAgreement);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    public Market getMarket(Long id) {
        try {
            return marketService.getMarketById(id);
        } catch (SQLException e) {
            System.err.println("Error fetching market: " + e.getMessage());
            return null;
        }
    }

    public List<Market> getAllMarkets() {
        try {
            return marketService.getAllMarkets();
        } catch (SQLException e) {
            System.err.println("Error fetching markets: " + e.getMessage());
            return List.of();
        }
    }

    public List<Market> getMergedMarkets() {
        try {
            return marketService.getMergedMarkets();
        } catch (SQLException e) {
            System.err.println("Error fetching markets: " + e.getMessage());
            return List.of();
        }
    }

    public List<String> getMergedAvailableCountries() {
        try {
            return marketService.getMergedAvailableCountries();
        } catch (Exception e) {
            System.err.println("Error fetching merged countries: " + e.getMessage());
            return List.of();
        }
    }

    public Map<String, Integer> getMergedCompanyCountByCountry() {
        try {
            return marketService.getMergedCompanyCountByCountry();
        } catch (Exception e) {
            System.err.println("Error fetching merged country counts: " + e.getMessage());
            return Map.of();
        }
    }

    public int getMergedCompanyCountForMarket(String countryCode, String countryName) {
        try {
            return marketService.getMergedCompanyCountForMarket(countryCode, countryName);
        } catch (Exception e) {
            System.err.println("Error fetching merged market count: " + e.getMessage());
            return 0;
        }
    }

    public int getMergedCompanyCountForMarket(String countryCode, String countryName, String region) {
        try {
            return marketService.getMergedCompanyCountForMarket(countryCode, countryName, region);
        } catch (Exception e) {
            System.err.println("Error fetching merged market count: " + e.getMessage());
            return 0;
        }
    }

    public List<Market> getEUMarkets() {
        try {
            return marketService.getEUMarkets();
        } catch (SQLException e) {
            System.err.println("Error fetching EU markets: " + e.getMessage());
            return List.of();
        }
    }

    public List<Market> searchMarkets(String query) {
        try {
            return marketService.searchMarkets(query);
        } catch (SQLException e) {
            System.err.println("Error searching markets: " + e.getMessage());
            return List.of();
        }
    }

    public boolean updateMarket(Market market) {
        try {
            return marketService.updateMarket(market);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean deleteMarket(Long marketId) {
        try {
            return marketService.deleteMarket(marketId);
        } catch (SQLException e) {
            System.err.println("Error deleting market: " + e.getMessage());
            return false;
        }
    }

    public List<CertificateRequirement> getMarketRequirements(Long marketId) {
        try {
            return marketService.getMarketRequirements(marketId);
        } catch (SQLException e) {
            System.err.println("Error fetching requirements: " + e.getMessage());
            return List.of();
        }
    }

    public List<CertificateRequirement> getRequirementsForProduct(Long marketId,
            ProductCategory category) {
        try {
            return marketService.getRequirementsForProduct(marketId, category);
        } catch (SQLException e) {
            System.err.println("Error fetching requirements: " + e.getMessage());
            return List.of();
        }
    }
}