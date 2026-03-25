import { useEffect } from "react";

export default function useBodyClass(className) {
  useEffect(() => {
    document.body.className = className;
    return () => { document.body.className = ''; };
  }, [className]);
}