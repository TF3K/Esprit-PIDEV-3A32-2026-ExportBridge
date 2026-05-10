package DAO;

import Entities.ProductCategory;
import Utils.DatabasePlugin;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProductCategoryDAO {

    public ProductCategory findById(Long id) throws SQLException {
        String sql = "SELECT * FROM product_categories WHERE id = ?";
        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, id);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next())
                    return mapRs(rs);
            }
        }
        return null;
    }

    public ProductCategory findByName(String name) throws SQLException {
        String sql = "SELECT * FROM product_categories WHERE name = ?";
        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, name);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next())
                    return mapRs(rs);
            }
        }
        return null;
    }

    public List<ProductCategory> findAll() throws SQLException {
        String sql = "SELECT * FROM product_categories ORDER BY name";
        List<ProductCategory> out = new ArrayList<>();
        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {
            while (rs.next())
                out.add(mapRs(rs));
        }
        return out;
    }

    public ProductCategory create(ProductCategory pc) throws SQLException {
        String sql = "INSERT INTO product_categories (name, description, slug) VALUES (?, ?, ?)";
        Connection conn = DatabasePlugin.getInstance().getConn();
        try (PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, pc.getName());
            stmt.setString(2, pc.getDescription());
            stmt.setString(3, pc.getSlug());
            stmt.executeUpdate();
            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    pc.setId(rs.getLong(1));
                }
            }
        }
        return pc;
    }

    private ProductCategory mapRs(ResultSet rs) throws SQLException {
        ProductCategory pc = new ProductCategory();
        pc.setId(rs.getLong("id"));
        pc.setName(rs.getString("name"));
        pc.setDescription(rs.getString("description"));
        pc.setSlug(rs.getString("slug"));
        return pc;
    }
}
