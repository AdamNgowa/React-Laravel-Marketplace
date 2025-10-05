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

    setSuccessMessages((prevMessages) => [newMessage, ...prevMessages]);

    const timeOutId = setTimeout(() => {
      setSuccessMessages((prevMessages) =>
        prevMessages.filter((msg) => msg.id !== newMessage.id)
      );
      delete timeOutRefs.current[newMessage.id];
    }, 5000);

    timeOutRefs.current[newMessage.id] = timeOutId;
  }, [props.success]);

  return (
    <div className="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
      <NavBar />

      {props.error && (
        <div className="container mx-auto px-4 sm:px-6 mt-4 text-red-500">
          <div className="alert alert-danger text-sm sm:text-base">
            {props.error}
          </div>
        </div>
      )}

      {successMessages.length > 0 && (
        <div className="toast toast-top right-2 left-2 sm:left-auto sm:right-4 z-[1000] mt-16 sm:mt-20">
          {successMessages.map((msg) => (
            <div
              className="alert alert-success text-sm sm:text-base shadow-lg"
              key={msg.id}
            >
              <span>{msg.message}</span>
            </div>
          ))}
        </div>
      )}

      <main className="flex-1 container mx-auto px-4 sm:px-6 py-6">
        {children}
      </main>
    </div>
  );
}
