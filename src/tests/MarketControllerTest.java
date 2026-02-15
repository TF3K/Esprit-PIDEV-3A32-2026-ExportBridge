import Controllers.MarketController;
import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;
import org.junit.jupiter.api.*;

import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class MarketControllerTest {

    private static MarketController marketController;
    private static boolean dataInitialized = false;

    @BeforeAll
    public static void setup() {
        marketController = new MarketController();
        initializeSampleDataIfNeeded();
    }

    private static void initializeSampleDataIfNeeded() {
        List<Market> existing = marketController.getAllMarkets();

        if (existing.isEmpty()) {
            createSampleMarket("FR", "France", "EU", true,
                    "Major trading partner", "EU-Tunisia Association Agreement");
            createSampleMarket("DE", "Germany", "EU", true,
                    "Largest economy in Europe", "EU-Tunisia Association Agreement");
            createSampleMarket("IT", "Italy", "EU", true,
                    "Close neighbor", "EU-Tunisia Association Agreement");

            dataInitialized = true;
        }
    }

    private static void createSampleMarket(String code, String name, String region,
                                           boolean isEU, String desc, String agreement) {
        marketController.createMarket(code, name, region, isEU, desc, agreement);
    }

    @Test
    @Order(1)
    @DisplayName("Test 1: Get All Markets")
    public void testGetAllMarkets() {
        List<Market> markets = marketController.getAllMarkets();

        assertNotNull(markets, "Should return market list");
        assertFalse(markets.isEmpty(), "Should have markets (verify DB has sample data)");

        markets.forEach(m -> System.out.println("  - " + m.getName() + " (" + m.getCountryCode() + ")"));
    }

    @Test
    @Order(2)
    @DisplayName("Test 2: Get EU Markets")
    public void testGetEUMarkets() {
        List<Market> euMarkets = marketController.getEUMarkets();

        assertNotNull(euMarkets, "Should return EU markets");
        assertFalse(euMarkets.isEmpty(), "Should have EU markets");
        assertTrue(euMarkets.stream().allMatch(Market::isEu), "All should be EU markets");

        euMarkets.forEach(m -> System.out.println("  - " + m.getName()));
    }

    @Test
    @Order(3)
    @DisplayName("Test 3: Search Markets")
    public void testSearchMarkets() {
        List<Market> franceResults = marketController.searchMarkets("France");

        assertNotNull(franceResults, "Should return search results");
        assertFalse(franceResults.isEmpty(), "Should find France");
        assertTrue(franceResults.stream().anyMatch(m -> m.getName().contains("France")),
                "Should contain France");
    }

    @Test
    @Order(4)
    @DisplayName("Test 4: Get Market by ID")
    public void testGetMarketById() {
        List<Market> markets = marketController.getAllMarkets();
        if (!markets.isEmpty()) {
            Long firstMarketId = markets.getFirst().getId();
            Market market = marketController.getMarket(firstMarketId);

            assertNotNull(market, "Should retrieve market");
            assertEquals(firstMarketId, market.getId());
        }
    }

    @Test
    @Order(5)
    @DisplayName("Test 5: Get Market Requirements")
    public void testGetMarketRequirements() {
        List<Market> markets = marketController.getAllMarkets();
        if (!markets.isEmpty()) {
            Long marketId = markets.get(0).getId();
            List<CertificateRequirement> requirements =
                    marketController.getMarketRequirements(marketId);

            assertNotNull(requirements, "Should return requirements list");

            if (!requirements.isEmpty()) {
                requirements.forEach(req ->
                        System.out.println("  - " + req.getCertificateType() +
                                " for " + req.getProductCategory() +
                                (req.isMandatory() ? " (Mandatory)" : " (Optional)"))
                );
            }
        }
    }

    @Test
    @Order(6)
    @DisplayName("Test 6: Get Requirements for Specific Product")
    public void testGetRequirementsForProduct() {
        List<Market> markets = marketController.getAllMarkets();
        if (!markets.isEmpty()) {
            Long marketId = markets.getFirst().getId();
            List<CertificateRequirement> oliveOilReqs =
                    marketController.getRequirementsForProduct(marketId, ProductCategory.OLIVE_OIL);

            assertNotNull(oliveOilReqs, "Should return requirements");

            if (!oliveOilReqs.isEmpty()) {
                oliveOilReqs.forEach(req ->
                        System.out.println("  - " + req.getCertificateType() +
                                (req.isMandatory() ? " (REQUIRED)" : " (Optional)"))
                );
            }
        }
    }

    @Test
    @Order(7)
    @DisplayName("Test 7: Verify Market Data Integrity")
    public void testMarketDataIntegrity() {
        List<Market> markets = marketController.getAllMarkets();

        for (Market market : markets) {
            assertNotNull(market.getId(), "Market should have ID");
            assertNotNull(market.getCountryCode(), "Market should have country code");
            assertNotNull(market.getName(), "Market should have name");
        }
    }
}