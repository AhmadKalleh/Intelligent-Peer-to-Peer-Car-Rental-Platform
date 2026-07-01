import styles from "./Dashboard.module.css";

function Dashboard() {
  return (
    <section className={styles.page}>
      <p className={styles.eyebrow}>STATISTICS</p>
      <h1>Welcome back, Admin</h1>
      <p className={styles.description}>
        Here is today’s overview of the car rental platform.
      </p>
    </section>
  );
}

export default Dashboard;
