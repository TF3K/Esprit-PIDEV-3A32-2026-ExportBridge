package Entities;

import java.util.List;

public class Market {
    private Long id;
    private String countryCode;
    private String name;
    private List<CertificateRequirement> requirements;
    private String region;
    private boolean isEu;
    private String description;
    private String tradeAgreement;
}
