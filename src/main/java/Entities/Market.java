package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.List;

@Data
@NoArgsConstructor
@AllArgsConstructor
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
