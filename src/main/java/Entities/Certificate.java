package Entities;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

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
        if (expiryDate == null) return false;

        LocalDateTime now = LocalDateTime.now();
        LocalDateTime thirtyDaysFromNow = now.plusDays(30);

        return expiryDate.isAfter(now) && expiryDate.isBefore(thirtyDaysFromNow);
    }
}
