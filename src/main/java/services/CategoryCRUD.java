package services;

import Utils.DBConnection;
import entites.Category;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CategoryCRUD {

    private final Connection cnx;

    public CategoryCRUD() {
        cnx = DBConnection.getConnection(); // ✅ correction
    }

    public List<Category> getAll() {
        List<Category> list = new ArrayList<>();
        String sql = "SELECT id, name FROM category";

        try (Statement st = cnx.createStatement();
             ResultSet rs = st.executeQuery(sql)) {

            while (rs.next()) {
                Category c = new Category();
                c.setId(rs.getInt("id"));
                c.setName(rs.getString("name"));
                list.add(c);
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }
        return list;
    }

    public void add(Category c) {
        String sql = "INSERT INTO category(name) VALUES(?)";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, c.getName());
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void update(Category c) {
        String sql = "UPDATE category SET name=? WHERE id=?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setString(1, c.getName());
            ps.setInt(2, c.getId());
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void delete(int id) {
        String sql = "DELETE FROM category WHERE id=?";
        try (PreparedStatement ps = cnx.prepareStatement(sql)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
