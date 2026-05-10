package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Data
@AllArgsConstructor
@NoArgsConstructor
public class Partnership {
    private Long id;
    private Long sourceCompanyId;
    private PartnershipStatus status;
    private PartnershipType type;
    private LocalDateTime establishedDate;
    private LocalDateTime terminatedDate;
    private String notes;
    private LocalDateTime createdAt;
    private LocalDateTime lastUpdated;

    public boolean isActive() {
        return status == PartnershipStatus.ACTIVE &&
                (terminatedDate == null || terminatedDate.isAfter(LocalDateTime.now()));
    }

    // Backwards-compatible accessors for legacy code that used source/target fields
    public Long getSourceCompanyId() {
        return this.sourceCompanyId;
    }

    public void setSourceCompanyId(Long id) {
        this.sourceCompanyId = id;
    }
}
