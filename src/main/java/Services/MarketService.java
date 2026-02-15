package Services;

import DAO.MarketDAO;
import DAO.CertificateRequirementDAO;
import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;

import java.sql.SQLException;
import java.util.List;

public class MarketService {
    private final MarketDAO marketDAO;
    private final CertificateRequirementDAO requirementDAO;

    public MarketService() {
        this.marketDAO = new MarketDAO();
        this.requirementDAO = new CertificateRequirementDAO();
    }

    public Market createMarket(String countryCode, String name, String region,
                               boolean isEU, String description, String tradeAgreement)
            throws SQLException {

        if (countryCode == null || countryCode.trim().isEmpty()) {
            throw new IllegalArgumentException("Country code is required");
        }

        if (name == null || name.trim().isEmpty()) {
            throw new IllegalArgumentException("Market name is required");
        }

        Market existing = marketDAO.findByCountryCode(countryCode);
        if (existing != null) {
            throw new IllegalArgumentException("Market with this country code already exists");
        }

        Market market = new Market();
        market.setCountryCode(countryCode.toUpperCase());
        market.setName(name);
        market.setRegion(region);
        market.setEu(isEU);
        market.setDescription(description);
        market.setTradeAgreement(tradeAgreement);

        return marketDAO.create(market);
    }

    public Market getMarketById(Long id) throws SQLException {
        return marketDAO.findById(id);
    }

    public Market getMarketByCountryCode(String countryCode) throws SQLException {
        return marketDAO.findByCountryCode(countryCode);
    }

    public List<Market> getAllMarkets() throws SQLException {
        return marketDAO.findAll();
    }

    public List<Market> getEUMarkets() throws SQLException {
        return marketDAO.findEUMarkets();
    }

    public List<Market> getMarketsByRegion(String region) throws SQLException {
        return marketDAO.findByRegion(region);
    }

    public List<Market> searchMarkets(String query) throws SQLException {
        if (query == null || query.trim().isEmpty()) {
            return getAllMarkets();
        }
        return marketDAO.search(query);
    }

    public boolean updateMarket(Market market) throws SQLException {
        if (market.getId() == null) {
            throw new IllegalArgumentException("Market ID is required for update");
        }

        if (market.getName() == null || market.getName().trim().isEmpty()) {
            throw new IllegalArgumentException("Market name is required");
        }

        return marketDAO.update(market);
    }

    public boolean deleteMarket(Long marketId) throws SQLException {
        return marketDAO.delete(marketId);
    }

    public List<CertificateRequirement> getMarketRequirements(Long marketId) throws SQLException {
        return requirementDAO.findByMarketId(marketId);
    }

    public List<CertificateRequirement> getRequirementsForProduct(Long marketId,
                                                                  ProductCategory category)
            throws SQLException {
        return requirementDAO.findByMarketAndCategory(marketId, category);
    }

    public List<CertificateRequirement> getMandatoryRequirements(Long marketId) throws SQLException {
        return requirementDAO.findMandatoryByMarket(marketId);
    }

    public CertificateRequirement addRequirement(CertificateRequirement requirement)
            throws SQLException {

        if (requirement.getMarketId() == null) {
            throw new IllegalArgumentException("Market ID is required");
        }

        if (requirement.getProductCategory() == null) {
            throw new IllegalArgumentException("Product category is required");
        }

        if (requirement.getCertificateType() == null) {
            throw new IllegalArgumentException("Certificate type is required");
        }

        return requirementDAO.create(requirement);
    }

    public long countMarkets() throws SQLException {
        return marketDAO.count();
    }

    public boolean marketExists(Long marketId) throws SQLException {
        return marketDAO.exists(marketId);
    }
}