package Services;

import DAO.MarketDAO;
import DAO.CertificateRequirementDAO;
import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;

import java.sql.SQLException;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.stream.Collectors;

public class MarketService {
    private final MarketDAO marketDAO;
    private final CertificateRequirementDAO requirementDAO;
    private final CompanySeedService companySeedService;

    public MarketService() {
        this.marketDAO = new MarketDAO();
        this.requirementDAO = new CertificateRequirementDAO();
        this.companySeedService = new CompanySeedService();
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
        Market merged = getMergedMarkets().stream()
                .filter(market -> market.getCountryCode() != null
                        && market.getCountryCode().equalsIgnoreCase(countryCode))
                .findFirst()
                .orElse(null);

        if (merged != null) {
            return merged;
        }

        return marketDAO.findByCountryCode(countryCode);
    }

    public List<Market> getAllMarkets() throws SQLException {
        return getMergedMarkets();
    }

    public List<Market> getEUMarkets() throws SQLException {
        return getMergedMarkets().stream()
                .filter(Market::isEu)
                .collect(Collectors.toList());
    }

    public List<Market> getMarketsByRegion(String region) throws SQLException {
        if (region == null || region.trim().isEmpty()) {
            return getMergedMarkets();
        }

        String lowerRegion = region.toLowerCase(Locale.ROOT).trim();
        return getMergedMarkets().stream()
                .filter(market -> market.getRegion() != null
                        && market.getRegion().toLowerCase(Locale.ROOT).contains(lowerRegion))
                .collect(Collectors.toList());
    }

    public List<Market> searchMarkets(String query) throws SQLException {
        if (query == null || query.trim().isEmpty()) {
            return getAllMarkets();
        }

        String lowerQuery = query.toLowerCase(Locale.ROOT).trim();
        return getMergedMarkets().stream()
                .filter(market -> containsIgnoreCase(market.getName(), lowerQuery)
                        || containsIgnoreCase(market.getCountryCode(), lowerQuery)
                        || containsIgnoreCase(market.getRegion(), lowerQuery)
                        || containsIgnoreCase(market.getDescription(), lowerQuery))
                .collect(Collectors.toList());
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
        return getMergedMarkets().size();
    }

    public boolean marketExists(Long marketId) throws SQLException {
        if (marketId == null) {
            return false;
        }

        if (marketDAO.exists(marketId)) {
            return true;
        }

        return getMergedMarkets().stream().anyMatch(market -> marketId.equals(market.getId()));
    }

    public List<Market> getMergedMarkets() throws SQLException {
        Map<String, Market> merged = new LinkedHashMap<>();

        for (Market seedMarket : buildSeedMarkets()) {
            merged.put(marketKey(seedMarket), copyMarket(seedMarket));
        }

        for (Market databaseMarket : marketDAO.findAll()) {
            merged.merge(marketKey(databaseMarket), copyMarket(databaseMarket), this::mergeMarkets);
        }

        return merged.values().stream()
                .sorted((left, right) -> left.getName().compareToIgnoreCase(right.getName()))
                .collect(Collectors.toList());
    }

    public Map<String, Integer> getMergedCompanyCountByCountry() {
        return companySeedService.getMergedCompanyCountByCountry();
    }

    public int getMergedCompanyCountForMarket(String countryCode, String countryName) {
        return companySeedService.getMergedCompaniesForMarket(countryCode, countryName, null).size();
    }

    public int getMergedCompanyCountForMarket(String countryCode, String countryName, String region) {
        return companySeedService.getMergedCompaniesForMarket(countryCode, countryName, region).size();
    }

    public List<String> getMergedAvailableCountries() {
        return companySeedService.getMergedAvailableCountries();
    }

    private List<Market> buildSeedMarkets() {
        return companySeedService.getMergedAvailableCountries().stream()
                .map(this::buildPseudoMarket)
                .collect(Collectors.toList());
    }

    private Market buildPseudoMarket(String country) {
        Market market = new Market();
        market.setCountryCode(resolveCountryCode(country));
        market.setName(country);
        market.setRegion(resolveRegion(country));
        market.setEu(isEuCountry(country));
        market.setDescription("Derived from trade datasets in resources/data");
        return market;
    }

    private Market mergeMarkets(Market existing, Market incoming) {
        Market merged = copyMarket(existing);
        if (incoming == null) {
            return merged;
        }

        if (!isBlank(incoming.getCountryCode()))
            merged.setCountryCode(incoming.getCountryCode());
        if (!isBlank(incoming.getName()))
            merged.setName(incoming.getName());
        if (!isBlank(incoming.getRegion()))
            merged.setRegion(incoming.getRegion());
        merged.setEu(incoming.isEu());
        if (!isBlank(incoming.getDescription()))
            merged.setDescription(incoming.getDescription());
        if (!isBlank(incoming.getTradeAgreement()))
            merged.setTradeAgreement(incoming.getTradeAgreement());
        if (incoming.getId() != null)
            merged.setId(incoming.getId());

        return merged;
    }

    private Market copyMarket(Market source) {
        if (source == null) {
            return new Market();
        }

        Market copy = new Market();
        copy.setId(source.getId());
        copy.setCountryCode(source.getCountryCode());
        copy.setName(source.getName());
        copy.setRequirements(source.getRequirements());
        copy.setRegion(source.getRegion());
        copy.setEu(source.isEu());
        copy.setDescription(source.getDescription());
        copy.setTradeAgreement(source.getTradeAgreement());
        return copy;
    }

    private String marketKey(Market market) {
        if (market == null) {
            return "";
        }

        if (!isBlank(market.getCountryCode())) {
            return market.getCountryCode().trim().toUpperCase(Locale.ROOT);
        }

        return resolveCountryCode(market.getName());
    }

    private String resolveCountryCode(String country) {
        if (country == null) {
            return "XX";
        }

        return switch (country) {
            case "France" -> "FR";
            case "Germany" -> "DE";
            case "Italy" -> "IT";
            case "Spain" -> "ES";
            case "Belgium" -> "BE";
            case "Netherlands" -> "NL";
            case "Portugal" -> "PT";
            case "Greece" -> "GR";
            case "Austria" -> "AT";
            case "Poland" -> "PL";
            case "United Kingdom" -> "GB";
            case "Sweden" -> "SE";
            case "Denmark" -> "DK";
            case "China" -> "CN";
            case "Japan" -> "JP";
            case "Singapore" -> "SG";
            case "Thailand" -> "TH";
            case "Malaysia" -> "MY";
            case "Indonesia" -> "ID";
            case "India" -> "IN";
            case "Taiwan" -> "TW";
            case "South Korea" -> "KR";
            case "United States" -> "US";
            case "South Africa" -> "ZA";
            case "United Arab Emirates" -> "AE";
            case "Tunisia" -> "TN";
            case "Tunisie" -> "TN";
            default -> "XX";
        };
    }

    private String resolveRegion(String country) {
        if (country == null) {
            return "International";
        }

        if (isWesternEurope(country))
            return "Western Europe";
        if (isSouthernEurope(country))
            return "Southern Europe";
        if (isCentralEurope(country))
            return "Central Europe";
        if (isAsian(country))
            return "Asia";
        if (country.equals("United States"))
            return "North America";
        if (country.equals("South Africa"))
            return "Africa";
        if (country.equals("Tunisia") || country.equals("Tunisie"))
            return "Africa";
        return "International";
    }

    private boolean isEuCountry(String country) {
        return country != null && (country.equals("France") || country.equals("Germany") ||
                country.equals("Italy") || country.equals("Spain") ||
                country.equals("Belgium") || country.equals("Netherlands") ||
                country.equals("Portugal") || country.equals("Greece"));
    }

    private boolean isWesternEurope(String country) {
        return country != null && (country.equals("France") || country.equals("Belgium") ||
                country.equals("Netherlands") || country.equals("United Kingdom"));
    }

    private boolean isSouthernEurope(String country) {
        return country != null && (country.equals("Italy") || country.equals("Spain") ||
                country.equals("Portugal") || country.equals("Greece"));
    }

    private boolean isCentralEurope(String country) {
        return country != null && (country.equals("Germany") || country.equals("Austria") ||
                country.equals("Poland"));
    }

    private boolean isAsian(String country) {
        return country != null && (country.equals("China") || country.equals("Japan") ||
                country.equals("Singapore") || country.equals("Thailand") ||
                country.equals("Malaysia") || country.equals("Indonesia") ||
                country.equals("India") || country.equals("Taiwan") ||
                country.equals("South Korea"));
    }

    private boolean containsIgnoreCase(String value, String lowerQuery) {
        return value != null && value.toLowerCase(Locale.ROOT).contains(lowerQuery);
    }

    private boolean isBlank(String value) {
        return value == null || value.trim().isEmpty();
    }
}