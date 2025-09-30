import { Link, usePage } from "@inertiajs/react";
import {
  PropsWithChildren,
  ReactNode,
  useEffect,
  useRef,
  useState,
} from "react";
import NavBar from "@/Components/App/NavBar";

export default function AuthenticatedLayout({
  header,
  children,
}: PropsWithChildren<{ header?: ReactNode }>) {
  const props = usePage().props;
  const user = props.auth.user;

  const [successMessages, setSuccessMessages] = useState<any[]>([]);
  const timeOutRefs = useRef<{ [key: number]: ReturnType<typeof setTimeout> }>(
    []
  );

  const [showingNavigationDropdown, setShowingNavigationDropdown] =
    useState(false);

  useEffect(() => {
    if (!props.success.message) return;
    const newMessage = { ...props.success, id: props.success.time };
    //Add new messages to the list
    setSuccessMessages((prevMessages) => [newMessage, ...prevMessages]);

    //Set a timeout for this specific message
    const timeOutId = setTimeout(() => {
      setSuccessMessages((prevMessages) =>
        prevMessages.filter((msg) => msg.id !== newMessage.id)
      );
      //Clear timeouts from refs after execution
      delete timeOutRefs.current[newMessage.id];
    }, 5000);

    //Store timeout ID in the ref
    timeOutRefs.current[newMessage.id] = timeOutId;
  }, [props.success]);

  return (
    <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
      <NavBar />
      {props.error && (
        <div className="container mx-auto px-8 mt-8 text-red-500">
          <div className="alert alert-danger">{props.error}</div>
        </div>
      )}

      {successMessages.length > 0 && (
        <div className="toast toast-top toast-end z-[1000] mt-16">
          {successMessages.map((msg) => (
            <div className="alert alert-success" key={msg.id}>
              {" "}
              <span>{msg.message}</span>{" "}
            </div>
          ))}
        </div>
      )}

      <main>{children}</main>
    </div>
  );
}
