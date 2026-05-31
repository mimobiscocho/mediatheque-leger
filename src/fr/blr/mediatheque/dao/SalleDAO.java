package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Salle;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des salles de coworking (CRUD). */
public class SalleDAO {

    public List<Salle> findAll() throws SQLException {
        List<Salle> list = new ArrayList<>();
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery("SELECT * FROM salle ORDER BY nom")) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public void insert(Salle s) throws SQLException {
        String sql = "INSERT INTO salle (nom, capacite, equipements, disponible) VALUES (?,?,?,?)";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            bind(ps, s);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) s.setId(keys.getInt(1));
            }
        }
    }

    public void update(Salle s) throws SQLException {
        String sql = "UPDATE salle SET nom=?, capacite=?, equipements=?, disponible=? WHERE id=?";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql)) {
            bind(ps, s);
            ps.setInt(5, s.getId());
            ps.executeUpdate();
        }
    }

    public void delete(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement("DELETE FROM salle WHERE id=?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public int count() throws SQLException {
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM salle")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    private void bind(PreparedStatement ps, Salle s) throws SQLException {
        ps.setString(1, s.getNom());
        ps.setInt(2, s.getCapacite());
        ps.setString(3, s.getEquipements());
        ps.setBoolean(4, s.isDisponible());
    }

    private Salle map(ResultSet rs) throws SQLException {
        Salle s = new Salle();
        s.setId(rs.getInt("id"));
        s.setNom(rs.getString("nom"));
        s.setCapacite(rs.getInt("capacite"));
        s.setEquipements(rs.getString("equipements"));
        s.setDisponible(rs.getBoolean("disponible"));
        return s;
    }
}
