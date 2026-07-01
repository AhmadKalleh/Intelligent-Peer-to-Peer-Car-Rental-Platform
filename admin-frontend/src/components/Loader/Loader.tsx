import styles from "./Loader.module.css";

type LoaderProps = {
  size?: "small" | "medium" | "large";
  color?: string;
};

function Loader({ size = "medium", color = "#0fe7f7" }: LoaderProps) {
  return (
    <span
      className={`${styles.riseLoader} ${styles[size]}`}
      style={{ "--loader-color": color } as React.CSSProperties}
    >
      <span></span>
      <span></span>
      <span></span>
      <span></span>
    </span>
  );
}

export default Loader;
