package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Reservation;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des réservations de salles. */
public class ReservationDAO {

    private static final String SELECT_BASE =
        "SELECT r.*, CONCAT(a.prenom, ' ', a.nom) AS adherent_nom, s.nom AS salle_nom "
        + "FROM reservation r "
        + "JOIN adherent a ON r.adherent_id = a.id "
        + "JOIN salle s    ON r.salle_id = s.id ";

    public List<Reservation> findAll() throws SQLException {
        List<Reservation> list = new ArrayList<>();
        String sql = SELECT_BASE + "ORDER BY r.date_reservation DESC, r.heure_debut";
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public int countActives() throws SQLException {
        String sql = "SELECT COUNT(*) FROM reservation WHERE statut = 'confirmee' AND date_reservation >= CURDATE()";
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    /**
     * Enregistre une réservation. Le chevauchement de créneau est contrôlé
     * par le trigger trg_reservation_before_insert (SQLException si conflit).
     */
    public void insert(Reservation r) throws SQLException {
        String sql = "INSERT INTO reservation "
            + "(adherent_id, salle_id, date_reservation, heure_debut, heure_fin, statut) "
            + "VALUES (?,?,?,?,?, 'confirmee')";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            ps.setInt(1, r.getAdherentId());
            ps.setInt(2, r.getSalleId());
            ps.setString(3, r.getDateReservation());
            ps.setString(4, r.getHeureDebut());
            ps.setString(5, r.getHeureFin());
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) r.setId(keys.getInt(1));
            }
        }
    }

    public void annuler(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement(
                "UPDATE reservation SET statut = 'annulee' WHERE id = ?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public void delete(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement("DELETE FROM reservation WHERE id=?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    private Reservation map(ResultSet rs) throws SQLException {
        Reservation r = new Reservation();
        r.setId(rs.getInt("id"));
        r.setAdherentId(rs.getInt("adherent_id"));
        r.setSalleId(rs.getInt("salle_id"));
        r.setDateReservation(rs.getString("date_reservation"));
        r.setHeureDebut(trimTime(rs.getString("heure_debut")));
        r.setHeureFin(trimTime(rs.getString("heure_fin")));
        r.setStatut(rs.getString("statut"));
        r.setAdherentNom(rs.getString("adherent_nom"));
        r.setSalleNom(rs.getString("salle_nom"));
        return r;
    }

    /** Convertit 'HH:mm:ss' en 'HH:mm' pour l'affichage. */
    private String trimTime(String time) {
        return (time != null && time.length() >= 5) ? time.substring(0, 5) : time;
    }
}
