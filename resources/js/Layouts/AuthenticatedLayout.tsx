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
    [],
  );

  const [showingNavigationDropdown, setShowingNavigationDropdown] =
    useState(false);

  useEffect(() => {
    if (!props.success.message) return;
    const newMessage = { ...props.success, id: props.success.time };

    setSuccessMessages((prevMessages) => [newMessage, ...prevMessages]);

    const timeOutId = setTimeout(() => {
      setSuccessMessages((prevMessages) =>
        prevMessages.filter((msg) => msg.id !== newMessage.id),
      );
      delete timeOutRefs.current[newMessage.id];
    }, 5000);

    timeOutRefs.current[newMessage.id] = timeOutId;
  }, [props.success]);

  return (
    <div className="min-h-screen bg-slate-100 text-slate-900 flex flex-col">
      <NavBar />

      {props.error && (
        <div className="container mx-auto mt-4 px-4 sm:px-6">
          <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:text-base">
            {props.error}
          </div>
        </div>
      )}

      {successMessages.length > 0 && (
        <div className="fixed left-2 right-2 top-20 z-[1000] sm:left-auto sm:right-4">
          <div className="space-y-2">
            {successMessages.map((msg) => (
              <div
                className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-lg sm:text-base"
                key={msg.id}
              >
                <span>{msg.message}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      <main className="container mx-auto flex-1 px-4 py-6 sm:px-6">
        {children}
      </main>
    </div>
  );
}
