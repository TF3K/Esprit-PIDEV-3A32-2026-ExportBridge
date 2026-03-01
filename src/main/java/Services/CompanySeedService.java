package Services;

import com.opencsv.CSVReader;
import com.opencsv.exceptions.CsvException;
import Entities.Company;

import java.io.*;
import java.nio.charset.StandardCharsets;
import java.time.LocalDateTime;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.stream.Collectors;

public class CompanySeedService {

    private static final Map<String, List<Company>> companiesCache = new ConcurrentHashMap<>();
    private static boolean isDataLoaded = false;

    private static final String[] CSV_FILES = {
            "/data/Import_Export_Trade_Data_EU.csv",
            "/data/Import_Export_Trade_Data_Asia.csv",
            "/data/Import_Export_Trade_Data_US.csv",
            "/data/Import_Export_Trade_Data_Africa.csv"
    };

    private static final Map<String, String> COUNTRY_MAPPING = new HashMap<>() {{
        put("NETHERLANDS", "Netherlands");
        put("GERMANY", "Germany");
        put("FRANCE", "France");
        put("ITALY", "Italy");
        put("SPAIN", "Spain");
        put("BELGIUM", "Belgium");
        put("PORTUGAL", "Portugal");
        put("GREECE", "Greece");
        put("UNITED KINGDOM", "United Kingdom");
        put("UK", "United Kingdom");
        put("AUSTRIA", "Austria");
        put("POLAND", "Poland");
        put("SWEDEN", "Sweden");
        put("DENMARK", "Denmark");

        put("CHINA", "China");
        put("JAPAN", "Japan");
        put("SINGAPORE", "Singapore");
        put("THAILAND", "Thailand");
        put("MALAYSIA", "Malaysia");
        put("INDONESIA", "Indonesia");
        put("VIETNAM", "Vietnam");
        put("INDIA", "India");
        put("TAIWAN", "Taiwan");
        put("SOUTH KOREA", "South Korea");

        put("UNITED STATES", "United States");
        put("USA", "United States");
        put("SOUTH AFRICA", "South Africa");
        put("UNITED ARAB EMIRATES", "United Arab Emirates");
        put("UAE", "United Arab Emirates");
    }};

    public CompanySeedService() {
        loadAllCompaniesFromCSV();
    }

    private void loadAllCompaniesFromCSV() {
        if (isDataLoaded) {
            return;
        }

        System.out.println("=== LOADING TRADE DATA ===");
        int totalLoaded = 0;

        for (String csvFile : CSV_FILES) {
            int loaded = loadCSVFile(csvFile);
            totalLoaded += loaded;
        }

        isDataLoaded = true;
        System.out.println("✓ Total: " + totalLoaded + " unique companies");
        System.out.println("✓ Countries: " + companiesCache.keySet().size());
        System.out.println("✓ Available markets: " + String.join(", ", companiesCache.keySet()));
        System.out.println("========================");
    }

    private int findColumnIndex(String[] header, String... possibleNames) {
        for (int i = 0; i < header.length; i++) {
            String headerCol = header[i].trim().toUpperCase();

            for (String name : possibleNames) {
                if (headerCol.equals(name.toUpperCase()) ||
                        headerCol.contains(name.toUpperCase()) ||
                        headerCol.replace(" ", "").equals(name.toUpperCase().replace(" ", ""))) {
                    return i;
                }
            }
        }
        return -1;
    }

    private int loadCSVFile(String csvPath) {
        int loaded = 0;
        Set<String> uniqueCompanies = new HashSet<>();

        try (InputStream is = getClass().getResourceAsStream(csvPath)) {

            if (is == null) {
                System.err.println("✗ File not found: " + csvPath);
                return 0;
            }

            try (InputStreamReader isr = new InputStreamReader(is, StandardCharsets.UTF_8);
                 CSVReader reader = new CSVReader(isr)) {

                System.out.println("⚙ Loading: " + csvPath);

                List<String[]> records = reader.readAll();

                if (records.isEmpty()) {
                    System.out.println("⚠ Empty file: " + csvPath);
                    return 0;
                }

                // Get header
                String[] header = records.get(0);

                // Find column indices
                int supplierNameIdx = findColumnIndex(header,
                        "SUPPLIER NAME", "SUPPLIER", "EXPORTER NAME", "EXPORTER", "COMPANY NAME");
                int supplierAddressIdx = findColumnIndex(header,
                        "SUPPLIER ADDRESS", "EXPORTER ADDRESS", "ADDRESS");
                int exportCountryIdx = findColumnIndex(header,
                        "EXPORT COUNTRY", "COUNTRY", "ORIGIN COUNTRY", "SUPPLIER COUNTRY");
                int productDescIdx = findColumnIndex(header,
                        "PRODUCT DESCRIPTION", "DESCRIPTION", "PRODUCT", "GOODS DESCRIPTION");

                if (supplierNameIdx == -1 || exportCountryIdx == -1) {
                    System.err.println("✗ Required columns not found in: " + csvPath);
                    return 0;
                }

                System.out.println("✓ Found columns - Supplier: [" + supplierNameIdx + "], Country: [" + exportCountryIdx + "], Product: [" + productDescIdx + "]");

                // Process records
                for (int i = 1; i < records.size(); i++) {
                    try {
                        String[] record = records.get(i);

                        if (record.length <= Math.max(supplierNameIdx, exportCountryIdx)) {
                            continue;
                        }

                        String supplierName = safeGetField(record, supplierNameIdx);
                        String supplierAddress = safeGetField(record, supplierAddressIdx);
                        String exportCountry = safeGetField(record, exportCountryIdx).toUpperCase();
                        String productDesc = safeGetField(record, productDescIdx);

                        if (supplierName.isEmpty() || exportCountry.isEmpty()) {
                            continue;
                        }

                        String normalizedCountry = COUNTRY_MAPPING.getOrDefault(exportCountry, exportCountry);

                        String uniqueKey = supplierName + "|" + normalizedCountry;
                        if (uniqueCompanies.contains(uniqueKey)) {
                            continue;
                        }
                        uniqueCompanies.add(uniqueKey);

                        // Create company
                        Company company = new Company();
                        company.setCompanyName(supplierName);
                        company.setAddress(supplierAddress);
                        company.setCountry(normalizedCountry);

                        // Extract contact info
                        extractContactInfo(company, supplierAddress);

                        // Determine business domain from name and product
                        company.setDomain(determineBusinessDomain(supplierName, productDesc));

                        // Generate other fields
                        company.setTaxNumber(generateTaxNumber(normalizedCountry));
                        company.setRegistrationNumber(generateRegistrationNumber());
                        company.setRating(3);
                        company.setWarnings(0);
                        company.setBanned(false);
                        company.setCreatedAt(LocalDateTime.now());
                        company.setLastUpdated(LocalDateTime.now());

                        // Add to cache
                        companiesCache
                                .computeIfAbsent(normalizedCountry, k -> new ArrayList<>())
                                .add(company);

                        loaded++;

                    } catch (Exception e) {
                        // Skip problematic records
                    }
                }

                System.out.println("✓ Loaded " + loaded + " unique companies from " + csvPath.substring(csvPath.lastIndexOf("/") + 1));
            }

        } catch (IOException | CsvException e) {
            System.err.println("✗ Failed to load " + csvPath + ": " + e.getMessage());
        }

        return loaded;
    }

    private String safeGetField(String[] record, int index) {
        if (index < 0 || index >= record.length) {
            return "";
        }
        String field = record[index];
        return field != null ? field.trim() : "";
    }

    private int findColumnIndex(String[] header, String columnName) {
        for (int i = 0; i < header.length; i++) {
            if (header[i].trim().equalsIgnoreCase(columnName)) {
                return i;
            }
        }
        return -1;
    }

    private void extractContactInfo(Company company, String address) {
        if (address == null || address.isEmpty()) {
            return;
        }

        try {
            String emailRegex = "[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}";
            java.util.regex.Pattern emailPattern = java.util.regex.Pattern.compile(emailRegex);
            java.util.regex.Matcher emailMatcher = emailPattern.matcher(address);
            if (emailMatcher.find()) {
                company.setContactEmail(emailMatcher.group().toLowerCase());
            }

            String phoneRegex = "\\+?[0-9]{1,4}[\\s.-]?\\(?[0-9]{1,4}\\)?[\\s.-]?[0-9]{3,4}[\\s.-]?[0-9]{4,}";
            java.util.regex.Pattern phonePattern = java.util.regex.Pattern.compile(phoneRegex);
            java.util.regex.Matcher phoneMatcher = phonePattern.matcher(address);
            if (phoneMatcher.find()) {
                company.setContactPhone(phoneMatcher.group());
            }
        } catch (Exception e) {
            // Ignore regex errors
        }
    }

    private String generateTaxNumber(String country) {
        try {
            String prefix = country != null && country.length() >= 2
                    ? country.substring(0, 2).toUpperCase()
                    : "XX";
            long random = (long)(Math.random() * 1000000000);
            return prefix + String.format("%09d", random);
        } catch (Exception e) {
            return "XX000000000";
        }
    }

    private String generateRegistrationNumber() {
        return "REG" + String.format("%08d", (int)(Math.random() * 100000000));
    }

    private String determineBusinessDomain(String companyName, String productDescription) {
        String searchText = (companyName + " " + productDescription).toUpperCase();

        if (searchText.contains("FOOD") || searchText.contains("AGRI") ||
                searchText.contains("DAIRY") || searchText.contains("FARM") ||
                searchText.contains("ORGANIC") || searchText.contains("BEVERAGE") ||
                searchText.contains("FRUIT") || searchText.contains("VEGETABLE")) {
            return "Food & Agriculture";
        }

        if (searchText.contains("PHARMA") || searchText.contains("MEDICAL") ||
                searchText.contains("HEALTH") || searchText.contains("VACCINE") ||
                searchText.contains("DRUG") || searchText.contains("HOSPITAL")) {
            return "Pharmaceuticals & Healthcare";
        }

        if (searchText.contains("TECH") || searchText.contains("DIGITAL") ||
                searchText.contains("SOFTWARE") || searchText.contains("ELECTRONIC") ||
                searchText.contains("COMPUTER") || searchText.contains("IT ")) {
            return "Technology & Electronics";
        }

        if (searchText.contains("TEXTILE") || searchText.contains("FASHION") ||
                searchText.contains("CLOTHING") || searchText.contains("GARMENT") ||
                searchText.contains("FABRIC") || searchText.contains("APPAREL")) {
            return "Textiles & Fashion";
        }

        if (searchText.contains("AUTO") || searchText.contains("MOTOR") ||
                searchText.contains("VEHICLE") || searchText.contains("TRANSPORT") ||
                searchText.contains("LOGISTICS")) {
            return "Automotive & Transport";
        }

        if (searchText.contains("CONSTRUCT") || searchText.contains("BUILD") ||
                searchText.contains("ENGINEER") || searchText.contains("INFRASTRUC")) {
            return "Construction & Engineering";
        }

        if (searchText.contains("ENERGY") || searchText.contains("POWER") ||
                searchText.contains("OIL") || searchText.contains("GAS") ||
                searchText.contains("RENEWABLE") || searchText.contains("SOLAR")) {
            return "Energy & Resources";
        }

        if (searchText.contains("CHEMICAL") || searchText.contains("PLASTIC") ||
                searchText.contains("MATERIAL") || searchText.contains("POLYMER")) {
            return "Chemicals & Materials";
        }

        if (searchText.contains("EXPORT") || searchText.contains("IMPORT") ||
                searchText.contains("TRADE") || searchText.contains("TRADING") ||
                searchText.contains("INTERNATIONAL")) {
            return "Import/Export & Trade";
        }

        if (searchText.contains("MANUFACT") || searchText.contains("INDUST") ||
                searchText.contains("FACTORY") || searchText.contains("PRODUCTION")) {
            return "Manufacturing";
        }

        if (searchText.contains("RETAIL") || searchText.contains("DISTRIBUT") ||
                searchText.contains("WHOLESALE") || searchText.contains("SUPPLY")) {
            return "Retail & Distribution";
        }

        if (searchText.contains("SERVICE") || searchText.contains("CONSULT") ||
                searchText.contains("SOLUTION")) {
            return "Business Services";
        }

        return "General Trading";
    }

    public List<Company> getCompaniesByCountry(String country) {
        List<Company> companies = companiesCache.get(country);

        if (companies == null || companies.isEmpty()) {
            Optional<Map.Entry<String, List<Company>>> match = companiesCache.entrySet().stream()
                    .filter(entry -> entry.getKey().toLowerCase().contains(country.toLowerCase()))
                    .findFirst();

            if (match.isPresent()) {
                companies = match.get().getValue();
                System.out.println("✓ Found " + companies.size() + " companies for " + match.get().getKey());
            } else {
                System.out.println("⚠ No companies found for: " + country);
                return new ArrayList<>();
            }
        }

        return new ArrayList<>(companies);
    }

    public List<Company> getRandomCompaniesByCountry(String country, int count) {
        List<Company> allCompanies = getCompaniesByCountry(country);

        if (allCompanies.isEmpty()) {
            return new ArrayList<>();
        }

        Collections.shuffle(allCompanies);
        return allCompanies.subList(0, Math.min(count, allCompanies.size()));
    }

    public List<String> getAvailableCountries() {
        return new ArrayList<>(companiesCache.keySet()).stream()
                .sorted()
                .collect(Collectors.toList());
    }

    public Map<String, Integer> getCompanyCountByCountry() {
        Map<String, Integer> counts = new HashMap<>();
        companiesCache.forEach((country, companies) ->
                counts.put(country, companies.size())
        );
        return counts;
    }

    public int getTotalCompaniesCount() {
        return companiesCache.values().stream()
                .mapToInt(List::size)
                .sum();
    }

    public List<Company> searchCompanies(String query, String country) {
        List<Company> companies = country != null ? getCompaniesByCountry(country) : getAllCompanies();
        String lowerQuery = query.toLowerCase();

        return companies.stream()
                .filter(c -> c.getCompanyName().toLowerCase().contains(lowerQuery))
                .limit(50)
                .collect(Collectors.toList());
    }

    public List<Company> getAllCompanies() {
        return companiesCache.values().stream()
                .flatMap(List::stream)
                .collect(Collectors.toList());
    }
}