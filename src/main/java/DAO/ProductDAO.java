package DAO;

import Entities.Product;
import Entities.ProductCategory;
import Utils.DatabasePlugin;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProductDAO implements GenericDAO<Product, Long> {

    @Override
    public Product create(Product product) throws SQLException {
        String sql = "INSERT INTO products (company_id, name, description, hs_code, " +
                "category, quantity, unit, unit_price, currency, origin_criteria) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setLong(1, product.getCompanyId());
            stmt.setString(2, product.getName());
            stmt.setString(3, product.getDescription());
            stmt.setString(4, product.getHsCode());
            stmt.setString(5, product.getCategory().name());
            stmt.setDouble(6, product.getQuantity() != null ? product.getQuantity() : 0);
            stmt.setString(7, product.getUnit());
            stmt.setDouble(8, product.getUnitPrice() != null ? product.getUnitPrice() : 0);
            stmt.setString(9, product.getCurrency() != null ? product.getCurrency() : "TND");
            stmt.setString(10, product.getOriginCriteria());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    product.setId(rs.getLong(1));
                }
            }
        }

        return product;
    }

    @Override
    public Product findById(Long id) throws SQLException {
        String sql = "SELECT * FROM products WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    @Override
    public List<Product> findAll() throws SQLException {
        String sql = "SELECT * FROM products ORDER BY name";
        List<Product> products = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                products.add(mapResultSetToEntity(rs));
            }
        }

        return products;
    }

    @Override
    public boolean update(Product product) throws SQLException {
        String sql = "UPDATE products SET company_id = ?, name = ?, description = ?, " +
                "hs_code = ?, category = ?, quantity = ?, unit = ?, " +
                "unit_price = ?, currency = ?, origin_criteria = ? WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, product.getCompanyId());
            stmt.setString(2, product.getName());
            stmt.setString(3, product.getDescription());
            stmt.setString(4, product.getHsCode());
            stmt.setString(5, product.getCategory().name());
            stmt.setDouble(6, product.getQuantity());
            stmt.setString(7, product.getUnit());
            stmt.setDouble(8, product.getUnitPrice());
            stmt.setString(9, product.getCurrency());
            stmt.setString(10, product.getOriginCriteria());
            stmt.setLong(11, product.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM products WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM products WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1) > 0;
                }
            }
        }

        return false;
    }

    @Override
    public long count() throws SQLException {
        String sql = "SELECT COUNT(*) FROM products";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    public List<Product> findByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM products WHERE company_id = ? ORDER BY name";
        List<Product> products = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    products.add(mapResultSetToEntity(rs));
                }
            }
        }

        return products;
    }

    public List<Product> findByCategory(ProductCategory category) throws SQLException {
        String sql = "SELECT * FROM products WHERE category = ? ORDER BY name";
        List<Product> products = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, category.name());

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    products.add(mapResultSetToEntity(rs));
                }
            }
        }

        return products;
    }

    private Product mapResultSetToEntity(ResultSet rs) throws SQLException {
        Product product = new Product();
        product.setId(rs.getLong("id"));
        product.setCompanyId(rs.getLong("company_id"));
        product.setName(rs.getString("name"));
        product.setDescription(rs.getString("description"));
        product.setHsCode(rs.getString("hs_code"));
        product.setCategory(ProductCategory.valueOf(rs.getString("category")));
        product.setQuantity(rs.getDouble("quantity"));
        product.setUnit(rs.getString("unit"));
        product.setUnitPrice(rs.getDouble("unit_price"));
        product.setCurrency(rs.getString("currency"));
        product.setOriginCriteria(rs.getString("origin_criteria"));
        return product;
    }
}