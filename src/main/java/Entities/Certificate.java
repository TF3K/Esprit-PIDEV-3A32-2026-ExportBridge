package Entities;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.List;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Certificate {
    private String documentFile;
    private String certificateNumber;
    private Long id;
    private Long companyId;
    private CertificateType type;
    private LocalDateTime issueDate;
    private LocalDateTime expiryDate;
    private String countryOfOrigin;
    private CertificateStatus status;
    private String issuingAuthority;

    public boolean isValid() {
        return status == CertificateStatus.VALID &&
                expiryDate != null &&
                LocalDateTime.now().isBefore(expiryDate);
    }

    public boolean isExpiringSoon() {
        return expiryDate != null && LocalDateTime.now().plusHours(1).isAfter(expiryDate);
    }
}
