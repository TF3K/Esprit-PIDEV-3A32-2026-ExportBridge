package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class CertificateRequirement {
    private Long id;
    private ProductCategory productCategory;
    private CertificateType requiredCertificate;
    private boolean mandatory;
    private String description;
    private Market targetMarket;
}
