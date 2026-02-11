package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.util.List;

@Data
@NoArgsConstructor
@AllArgsConstructor
public class Company {
    private Long id;
    private String companyName;
    private String domain;
    private String taxNumber;
    private String registrationNumber;
    private String country;
    private String address;
    private String contactEmail;
    private String contactPhone;
    private Integer rating;
    private Integer warnings;
    private boolean isBanned;
    private Long companyManagerId;
}
