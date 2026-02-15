package Controllers;

import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;
import Services.MarketService;

import java.sql.SQLException;
import java.util.List;

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